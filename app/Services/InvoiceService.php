<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceService
{
    /**
     * Generate PDF binary content and metadata for an order
     *
     * @param string $orderNumber
     * @return array|null ['pdf_binary' => string, 'filename' => string, 'order' => array, 'user' => array, 'html' => string]
     */
    public static function generateInvoicePdf(string $orderNumber): ?array
    {
        $db = \Core\Database::getInstance();

        // Fetch CMS configurations for Logo and GST
        $cmsLogo = '';
        $cmsGst = '33BFGPG7507C2ZW';

        $navStmt = $db->query("SELECT content_data FROM cms_components WHERE section_type = 'navbar' AND is_active = 1 ORDER BY id DESC LIMIT 1");
        $navComp = $navStmt->fetch(\PDO::FETCH_ASSOC);
        if ($navComp && !empty($navComp['content_data'])) {
            $navData = json_decode($navComp['content_data'], true);
            $cmsLogo = $navData['global_settings']['logo'] ?? $navData['logo'] ?? '';
            if (!empty($navData['global_settings']['gst_number'])) $cmsGst = $navData['global_settings']['gst_number'];
            elseif (!empty($navData['global_settings']['gst'])) $cmsGst = $navData['global_settings']['gst'];
            elseif (!empty($navData['gst_number'])) $cmsGst = $navData['gst_number'];
            elseif (!empty($navData['gst'])) $cmsGst = $navData['gst'];
        }

        // Fetch order with buyer details
        $sql = "
            SELECT o.*, u.name as buyer_name, u.email as buyer_email, u.phone as buyer_phone,
                   bp.business_name, bp.gst_number as buyer_gst_number
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN business_profiles bp ON u.id = bp.user_id
            WHERE o.order_number = ?
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Fetch order items with dynamic category, product, and variant tax rates
        $iStmt = $db->prepare("
            SELECT oi.*, p.name as product_name, p.weight, p.wholesale_price,
                   p.sgst as product_sgst, p.cgst as product_cgst, p.total_gst as product_total_gst,
                   pv.name as variant_name, pv.base_price as variant_base_price, pv.discount_price as variant_discount_price,
                   pv.sgst as variant_sgst, pv.cgst as variant_cgst, pv.total_gst as variant_total_gst,
                   pv.max_amount as variant_max_amount,
                   c.sgst as category_sgst, c.cgst as category_cgst, c.hsn_code as category_hsn_code,
                   pi.image_path as product_image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN (SELECT product_id, MIN(image_path) as image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) pi ON p.id = pi.product_id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        $iStmt->execute([$order['id']]);
        $items = $iStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch modification if exists
        $modStmt = $db->prepare("
            SELECT om.*, a.name as admin_name
            FROM order_modifications om
            JOIN users a ON om.modified_by = a.id
            JOIN orders o ON om.order_id = o.id
            WHERE om.order_id = ? AND om.status IN ('accepted', 'applied') AND om.created_at >= o.created_at
            ORDER BY om.created_at DESC LIMIT 1
        ");
        $modStmt->execute([$order['id']]);
        $modification = $modStmt->fetch(\PDO::FETCH_ASSOC);

        // Fetch modification items if modification exists
        $modItems = [];
        if ($modification) {
            $miStmt = $db->prepare("
                SELECT omi.*,
                       COALESCE(p.name, p2.name) as product_name,
                       COALESCE(pv.name, pv2.name) as variant_name,
                       COALESCE(pi1.image_path, pi2.image_path) as product_image,
                       COALESCE(c.hsn_code, c2.hsn_code, '5208') as category_hsn_code,
                       COALESCE(pv.sgst, pv2.sgst, p.sgst, p2.sgst, c.sgst, c2.sgst, 0) as sgst_rate,
                       COALESCE(pv.cgst, pv2.cgst, p.cgst, p2.cgst, c.cgst, c2.cgst, 0) as cgst_rate,
                       COALESCE(pv.total_gst, pv2.total_gst, p.total_gst, p2.total_gst, (COALESCE(c.sgst, c2.sgst, 0) + COALESCE(c.cgst, c2.cgst, 0)), 0) as total_gst_rate,
                       COALESCE(pv.base_price, pv2.base_price, p.wholesale_price, p2.wholesale_price, 0) as base_price,
                       oi.customization_data,
                       oi.customization_id
                FROM order_modification_items omi
                LEFT JOIN order_items oi ON omi.order_item_id = oi.id
                LEFT JOIN products p ON oi.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                LEFT JOIN (SELECT product_id, MIN(image_path) as image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) pi1 ON p.id = pi1.product_id
                LEFT JOIN products p2 ON omi.product_id = p2.id
                LEFT JOIN categories c2 ON p2.category_id = c2.id
                LEFT JOIN product_variants pv2 ON omi.variant_id = pv2.id
                LEFT JOIN (SELECT product_id, MIN(image_path) as image_path FROM product_images WHERE is_primary = 1 GROUP BY product_id) pi2 ON p2.id = pi2.product_id
                WHERE omi.modification_id = ?
                ORDER BY omi.change_type ASC
            ");
            $miStmt->execute([$modification['id']]);
            $modItems = $miStmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Fetch shipping address
        $addrStmt = $db->prepare("SELECT * FROM addresses WHERE id = ? LIMIT 1");
        $addrStmt->execute([$order['shipping_address_id'] ?? 0]);
        $address = $addrStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$address && !empty($order['user_id'])) {
            $uAddrStmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC LIMIT 1");
            $uAddrStmt->execute([$order['user_id']]);
            $address = $uAddrStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }

        $html = self::buildHtml($order, $items, $modification, $modItems, $address, $cmsLogo, $cmsGst);
        $invoiceNum = 'INV-' . strtoupper($order['order_number']);

        if (!extension_loaded('gd')) {
            return [
                'is_html'    => true,
                'html'       => $html,
                'filename'   => $invoiceNum . '.html',
                'order'      => $order,
                'user'       => [
                    'name'  => $order['buyer_name'],
                    'email' => $order['buyer_email'],
                    'phone' => $order['buyer_phone']
                ]
            ];
        }

        try {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', PUBLIC_PATH);
            $options->set('defaultFont', 'DejaVu Sans');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfBinary = $dompdf->output();

            return [
                'is_html'    => false,
                'pdf_binary' => $pdfBinary,
                'filename'   => $invoiceNum . '.pdf',
                'order'      => $order,
                'user'       => [
                    'name'  => $order['buyer_name'],
                    'email' => $order['buyer_email'],
                    'phone' => $order['buyer_phone']
                ],
                'html'       => $html
            ];
        } catch (\Throwable $e) {
            return [
                'is_html'    => true,
                'html'       => $html,
                'filename'   => $invoiceNum . '.html',
                'order'      => $order,
                'user'       => [
                    'name'  => $order['buyer_name'],
                    'email' => $order['buyer_email'],
                    'phone' => $order['buyer_phone']
                ]
            ];
        }
    }

    /**
     * Convert currency amount (INR) to words
     */
    public static function numberToWordsIndian(float $number): string
    {
        $number = round($number, 2);
        $whole = (int)floor($number);
        $fraction = (int)round(($number - $whole) * 100);

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];

        if ($whole === 0 && $fraction === 0) {
            return 'Rupees Zero Only';
        }

        $convertGroup = function($num) use ($words) {
            $str = '';
            if ($num >= 100) {
                $str .= $words[(int)floor($num / 100)] . ' Hundred ';
                $num %= 100;
            }
            if ($num > 0) {
                if ($num < 20) {
                    $str .= $words[$num] . ' ';
                } else {
                    $str .= $words[(int)floor($num / 10) * 10] . ' ';
                    if ($num % 10 > 0) {
                        $str .= $words[$num % 10] . ' ';
                    }
                }
            }
            return trim($str);
        };

        $result = '';
        $crore = (int)floor($whole / 10000000);
        $whole %= 10000000;
        $lakh = (int)floor($whole / 100000);
        $whole %= 100000;
        $thousand = (int)floor($whole / 1000);
        $whole %= 1000;
        $hundred = $whole;

        if ($crore > 0) {
            $result .= $convertGroup($crore) . ' Crore ';
        }
        if ($lakh > 0) {
            $result .= $convertGroup($lakh) . ' Lakh ';
        }
        if ($thousand > 0) {
            $result .= $convertGroup($thousand) . ' Thousand ';
        }
        if ($hundred > 0) {
            $result .= $convertGroup($hundred) . ' ';
        }

        $result = trim($result);
        $text = 'Rupees ' . ($result !== '' ? $result : 'Zero');

        if ($fraction > 0) {
            $paiseStr = $convertGroup($fraction);
            $text .= ' and ' . $paiseStr . ' Paise';
        }

        return $text . ' Only';
    }

    private static function formatCleanAddress($addrString): string
    {
        if (empty($addrString)) return '';
        $lines = array_map('trim', explode("\n", $addrString));
        $uniqueLines = [];
        foreach ($lines as $line) {
            $cleaned = trim($line, " \t\n\r\0\x0B,");
            if ($cleaned !== '' && !in_array($cleaned, $uniqueLines)) {
                $uniqueLines[] = htmlspecialchars($cleaned);
            }
        }
        return implode('<br>', $uniqueLines);
    }

    /**
     * Determine if order/address is within Tamil Nadu (Intra-state: CGST + SGST) or outside (Inter-state: IGST)
     */
    public static function isTamilNaduAddress($order, $address = null): bool
    {
        // 1. Determine primary address text (Order shipping address is highest authority)
        $addrText = '';
        if (is_array($order) && !empty(trim($order['shipping_address'] ?? ''))) {
            $addrText = trim($order['shipping_address']);
        } elseif (is_array($order) && !empty(trim($order['shipping_state'] ?? ''))) {
            $addrText = trim($order['shipping_state']);
        } elseif (is_array($address)) {
            $pieces = [];
            foreach (['state', 'city', 'line1', 'line2', 'address_line1', 'address_line2', 'postal_code', 'pincode'] as $k) {
                if (!empty($address[$k])) $pieces[] = $address[$k];
            }
            $addrText = implode(' ', $pieces);
        }

        $lower = strtolower($addrText);
        if (empty(trim($lower))) {
            return true; // Default to Tamil Nadu for local store
        }

        // 2. Check for explicit other state names or major other-state cities
        $otherStates = [
            'kerala', 'karnataka', 'andhra pradesh', 'andhra', 'telangana', 'maharashtra',
            'gujarat', 'rajasthan', 'uttar pradesh', 'madhya pradesh', 'bihar', 'west bengal',
            'punjab', 'haryana', 'odisha', 'assam', 'goa', 'delhi', 'himachal pradesh',
            'jharkhand', 'chhattisgarh', 'uttarakhand', 'tripura', 'meghalaya', 'manipur',
            'nagaland', 'bengaluru', 'bangalore', 'mumbai', 'hyderabad', 'kochi',
            'calicut', 'kannur', 'trivandrum', 'thiruvananthapuram', 'ernakulam', 'kolkata'
        ];
        foreach ($otherStates as $os) {
            if (preg_match('/\b' . preg_quote($os, '/') . '\b/i', $lower)) {
                if (!preg_match('/\b(tamil\s*nadu|tamilnadu|\btn\b)/i', $lower)) {
                    return false;
                }
            }
        }

        // 3. Explicit Tamil Nadu checks (state name or acronym)
        if (preg_match('/\b(tamil\s*nadu|tamilnadu|\btn\b)/i', $lower)) {
            return true;
        }

        // 4. Tamil Nadu district/city names
        $tnCities = [
            'chennai', 'coimbatore', 'madurai', 'tiruchirappalli', 'trichy', 'salem',
            'tiruppur', 'tirupur', 'erode', 'vellore', 'thoothukudi', 'tuticorin',
            'dindigul', 'thanjavur', 'ranipet', 'sivakasi', 'karur', 'hosur',
            'nagercoil', 'kanchipuram', 'kumarapalayam', 'karaikkudi', 'neyveli',
            'cuddalore', 'kumbakonam', 'tiruvannamalai', 'pollachi', 'rajapalayam',
            'pudukkottai', 'nagapattinam', 'sulur', 'kadampadi', 'dharmapuri',
            'krishnagiri', 'namakkal', 'perambalur', 'ramanathapuram', 'theni',
            'thiruvallur', 'thiruvarur', 'tirunelveli', 'tirupattur', 'viluppuram',
            'virudhunagar', 'ariyalur', 'chengalpattu', 'kallakurichi', 'tenkasi'
        ];
        foreach ($tnCities as $city) {
            if (preg_match('/\b' . preg_quote($city, '/') . '\b/i', $lower)) {
                return true;
            }
        }

        // 5. Check 6-digit Indian Pincode prefix (Tamil Nadu postal circle is 60-64)
        if (preg_match('/\b([1-9][0-9]{5})\b/', $lower, $m)) {
            $pinPrefix = substr($m[1], 0, 2);
            if (in_array($pinPrefix, ['60', '61', '62', '63', '64'])) {
                return true;
            } else {
                return false;
            }
        }

        // Default to true (Tamil Nadu)
        return true;
    }

    public static function buildHtml($order, $items, $modification, $modItems, $address, $cmsLogo = '', $cmsGst = '33BFGPG7507C2ZW'): string
    {
        $hasModification = !empty($modification);
        $invoiceDate = date('d M Y', strtotime($order['created_at']));
        $invoiceNum = 'INV-' . strtoupper($order['order_number']);
        $isTamilNadu = self::isTamilNaduAddress($order, $address);

        $changeTypeLabel = [
            'removed'       => 'Removed',
            'qty_changed'   => 'Qty Changed',
            'price_changed' => 'Price Changed',
            'added'         => 'New Added',
            'unchanged'     => 'Unchanged',
        ];
        $changeTypeBg = [
            'removed'       => '#fee2e2',
            'qty_changed'   => '#fef3c7',
            'price_changed' => '#ede9fe',
            'added'         => '#d1fae5',
            'unchanged'     => '#f3f4f6',
        ];
        $changeTypeColor = [
            'removed'       => '#991b1b',
            'qty_changed'   => '#92400e',
            'price_changed' => '#5b21b6',
            'added'         => '#065f46',
            'unchanged'     => '#6b7280',
        ];

        // Process original items with dynamic tax calculations and rate breakdown
        $totalBase = 0;
        $totalSgst = 0;
        $totalCgst = 0;
        $totalIgst = 0;
        $cgstBreakdown = [];
        $sgstBreakdown = [];
        $igstBreakdown = [];
        $taxTiers = [];
        $calculatedItems = [];

        $sumOriginalItems = 0.0;
        foreach ($items as $item) {
            $sumOriginalItems += ((int)($item['quantity'] ?? 0)) * ((float)($item['unit_price'] ?? 0));
        }
        $orderGrandTotal = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
        $isOrigPriceExclusive = ($orderGrandTotal > ($sumOriginalItems + 0.01));

        foreach ($items as $item) {
            $qty = (int)($item['quantity'] ?? 0);
            $unitPriceInclGst = (float)($item['unit_price'] ?? 0);

            // Fetch dynamic SGST and CGST rates
            $sgstPercent = 0.0;
            $cgstPercent = 0.0;
            if (isset($item['variant_sgst']) && (float)$item['variant_sgst'] > 0 || isset($item['variant_cgst']) && (float)$item['variant_cgst'] > 0) {
                $sgstPercent = (float)($item['variant_sgst'] ?? 0);
                $cgstPercent = (float)($item['variant_cgst'] ?? 0);
            } elseif (isset($item['product_sgst']) && (float)$item['product_sgst'] > 0 || isset($item['product_cgst']) && (float)$item['product_cgst'] > 0) {
                $sgstPercent = (float)($item['product_sgst'] ?? 0);
                $cgstPercent = (float)($item['product_cgst'] ?? 0);
            } elseif (isset($item['category_sgst']) && (float)$item['category_sgst'] > 0 || isset($item['category_cgst']) && (float)$item['category_cgst'] > 0) {
                $sgstPercent = (float)($item['category_sgst'] ?? 0);
                $cgstPercent = (float)($item['category_cgst'] ?? 0);
            }

            $totalGstPercent = $sgstPercent + $cgstPercent;
            if ($totalGstPercent <= 0) {
                $variantTotGst = (float)($item['variant_total_gst'] ?? 0);
                $prodTotGst = (float)($item['product_total_gst'] ?? 0);
                $tot = $variantTotGst > 0 ? $variantTotGst : $prodTotGst;
                if ($tot > 0) {
                    $sgstPercent = round($tot / 2, 2);
                    $cgstPercent = round($tot / 2, 2);
                    $totalGstPercent = $tot;
                }
            }

            // Fallback for custom fabric orders or items with customization_data
            if ($totalGstPercent <= 0 && !empty($item['customization_data'])) {
                $cData = is_array($item['customization_data']) ? $item['customization_data'] : json_decode($item['customization_data'], true);
                if (!empty($cData['gst_rate'])) {
                    $totalGstPercent = (float)$cData['gst_rate'];
                } elseif (!empty($cData['fabric_cost']) && !empty($cData['gst_amount'])) {
                    $totalGstPercent = round(((float)$cData['gst_amount'] / (float)$cData['fabric_cost']) * 100, 1);
                }
            }
            if ($totalGstPercent <= 0 && (!empty($item['customization_id']) || !empty($item['customization_data']) || stripos($item['product_name'] ?? '', 'fabric') !== false)) {
                $totalGstPercent = 5.0;
            }
            if ($totalGstPercent > 0 && $sgstPercent <= 0 && $cgstPercent <= 0) {
                $sgstPercent = round($totalGstPercent / 2, 2);
                $cgstPercent = round($totalGstPercent / 2, 2);
            }

            if ($isOrigPriceExclusive) {
                $unitBase = $unitPriceInclGst;
                $lineBase = round($unitBase * $qty, 2);
            } elseif ($totalGstPercent > 0) {
                $unitBase = round($unitPriceInclGst / (1 + ($totalGstPercent / 100)), 2);
                $lineBase = round(($unitPriceInclGst * $qty) / (1 + ($totalGstPercent / 100)), 2);
            } else {
                $unitBase = $unitPriceInclGst;
                $lineBase = round($unitBase * $qty, 2);
            }

            if ($isTamilNadu) {
                $lineTotalTax = $totalGstPercent > 0 ? round($lineBase * ($totalGstPercent / 100), 2) : 0.0;
                if ($cgstPercent + $sgstPercent > 0) {
                    $lineCgst = round($lineTotalTax * ($cgstPercent / ($cgstPercent + $sgstPercent)), 2);
                    $lineSgst = round($lineTotalTax - $lineCgst, 2);
                } else {
                    $lineCgst = 0.0;
                    $lineSgst = 0.0;
                }
                $lineIgst = 0.0;

                $cgstKey = (string)(float)$cgstPercent . '%';
                $sgstKey = (string)(float)$sgstPercent . '%';
                $cgstBreakdown[$cgstKey] = ($cgstBreakdown[$cgstKey] ?? 0.0) + $lineCgst;
                $sgstBreakdown[$sgstKey] = ($sgstBreakdown[$sgstKey] ?? 0.0) + $lineSgst;
            } else {
                $lineSgst = 0.0;
                $lineCgst = 0.0;
                $lineTotalTax = $totalGstPercent > 0 ? round($lineBase * ($totalGstPercent / 100), 2) : 0.0;
                $lineIgst = $lineTotalTax;

                $igstKey = (string)(float)$totalGstPercent . '%';
                $igstBreakdown[$igstKey] = ($igstBreakdown[$igstKey] ?? 0.0) + $lineIgst;
            }

            $tierKey = (string)(float)$totalGstPercent . '%';
            if (!isset($taxTiers[$tierKey])) {
                $taxTiers[$tierKey] = [
                    'total_rate' => (float)$totalGstPercent,
                    'cgst_rate' => $isTamilNadu ? (float)$cgstPercent : 0.0,
                    'sgst_rate' => $isTamilNadu ? (float)$sgstPercent : 0.0,
                    'igst_rate' => $isTamilNadu ? 0.0 : (float)$totalGstPercent,
                    'cgst_amount' => 0.0,
                    'sgst_amount' => 0.0,
                    'igst_amount' => 0.0,
                ];
            }
            $taxTiers[$tierKey]['cgst_amount'] += $lineCgst;
            $taxTiers[$tierKey]['sgst_amount'] += $lineSgst;
            $taxTiers[$tierKey]['igst_amount'] += $lineIgst;

            $lineTotal = round($lineBase + $lineTotalTax, 2);

            $totalBase += $lineBase;
            $totalSgst += $lineSgst;
            $totalCgst += $lineCgst;
            $totalIgst += $lineIgst;

            $calculatedItems[] = array_merge($item, [
                'unit_base' => $unitBase,
                'line_base' => $lineBase,
                'sgst_percent' => $sgstPercent,
                'cgst_percent' => $cgstPercent,
                'total_gst_percent' => $totalGstPercent,
                'line_sgst' => $lineSgst,
                'line_cgst' => $lineCgst,
                'line_igst' => $lineIgst,
                'line_total' => $lineTotal
            ]);
        }

        // Process modification items if modified
        $modTotalBase = 0;
        $modTotalSgst = 0;
        $modTotalCgst = 0;
        $modTotalIgst = 0;
        $modCgstBreakdown = [];
        $modSgstBreakdown = [];
        $modIgstBreakdown = [];
        $modTaxTiers = [];
        $calculatedModItems = [];

        if ($hasModification && !empty($modItems)) {
            $revisedTotal = (float)($modification['revised_total'] ?? 0);
            $sumNewItems = 0.0;
            foreach ($modItems as $m) {
                if (($m['change_type'] ?? '') !== 'removed') {
                    $sumNewItems += ((int)($m['new_qty'] ?? 0)) * ((float)($m['new_price'] ?? 0));
                }
            }
            // If revised total is greater than sum of new item prices, prices were entered exclusive of GST (with GST added as fee)
            $isPriceExclusive = ($revisedTotal > ($sumNewItems + 0.01));

            foreach ($modItems as $mc) {
                $ct = $mc['change_type'];
                $newQty = (int)($mc['new_qty'] ?? 0);
                $newPrice = (float)($mc['new_price'] ?? 0);
                
                $sgstPercent = (float)($mc['sgst_rate'] ?? 0);
                $cgstPercent = (float)($mc['cgst_rate'] ?? 0);
                $totalGstPercent = (float)($mc['total_gst_rate'] ?? ($sgstPercent + $cgstPercent));

                // Fallback from customization_data if tax rates were missing
                if ($totalGstPercent <= 0 && !empty($mc['customization_data'])) {
                    $cData = is_array($mc['customization_data']) ? $mc['customization_data'] : json_decode($mc['customization_data'], true);
                    if (!empty($cData['gst_rate'])) {
                        $totalGstPercent = (float)$cData['gst_rate'];
                    } elseif (!empty($cData['fabric_cost']) && !empty($cData['gst_amount'])) {
                        $totalGstPercent = round(((float)$cData['gst_amount'] / (float)$cData['fabric_cost']) * 100, 1);
                    }
                }
                if ($totalGstPercent <= 0 && (!empty($mc['customization_id']) || !empty($mc['customization_data']) || stripos($mc['product_name'] ?? '', 'fabric') !== false)) {
                    $totalGstPercent = 5.0;
                }
                if ($totalGstPercent > 0 && $sgstPercent <= 0 && $cgstPercent <= 0) {
                    $sgstPercent = round($totalGstPercent / 2, 2);
                    $cgstPercent = round($totalGstPercent / 2, 2);
                }

                if ($ct !== 'removed') {
                    if ($isPriceExclusive) {
                        $unitBase = $newPrice;
                        $lineBase = round($unitBase * $newQty, 2);
                    } elseif ($totalGstPercent > 0) {
                        $unitBase = round($newPrice / (1 + ($totalGstPercent / 100)), 2);
                        $lineBase = round(($newPrice * $newQty) / (1 + ($totalGstPercent / 100)), 2);
                    } else {
                        $unitBase = $newPrice;
                        $lineBase = round($unitBase * $newQty, 2);
                    }

                    if ($isTamilNadu) {
                        $lineTotalTax = $totalGstPercent > 0 ? round($lineBase * ($totalGstPercent / 100), 2) : 0.0;
                        if ($cgstPercent + $sgstPercent > 0) {
                            $lineCgst = round($lineTotalTax * ($cgstPercent / ($cgstPercent + $sgstPercent)), 2);
                            $lineSgst = round($lineTotalTax - $lineCgst, 2);
                        } else {
                            $lineCgst = 0.0;
                            $lineSgst = 0.0;
                        }
                        $lineIgst = 0.0;

                        $cgstKey = (string)(float)$cgstPercent . '%';
                        $sgstKey = (string)(float)$sgstPercent . '%';
                        $modCgstBreakdown[$cgstKey] = ($modCgstBreakdown[$cgstKey] ?? 0.0) + $lineCgst;
                        $modSgstBreakdown[$sgstKey] = ($modSgstBreakdown[$sgstKey] ?? 0.0) + $lineSgst;
                    } else {
                        $lineSgst = 0.0;
                        $lineCgst = 0.0;
                        $lineTotalTax = $totalGstPercent > 0 ? round($lineBase * ($totalGstPercent / 100), 2) : 0.0;
                        $lineIgst = $lineTotalTax;

                        $igstKey = (string)(float)$totalGstPercent . '%';
                        $modIgstBreakdown[$igstKey] = ($modIgstBreakdown[$igstKey] ?? 0.0) + $lineIgst;
                    }

                    $tierKey = (string)(float)$totalGstPercent . '%';
                    if (!isset($modTaxTiers[$tierKey])) {
                        $modTaxTiers[$tierKey] = [
                            'total_rate' => (float)$totalGstPercent,
                            'cgst_rate' => $isTamilNadu ? (float)$cgstPercent : 0.0,
                            'sgst_rate' => $isTamilNadu ? (float)$sgstPercent : 0.0,
                            'igst_rate' => $isTamilNadu ? 0.0 : (float)$totalGstPercent,
                            'cgst_amount' => 0.0,
                            'sgst_amount' => 0.0,
                            'igst_amount' => 0.0,
                        ];
                    }
                    $modTaxTiers[$tierKey]['cgst_amount'] += $lineCgst;
                    $modTaxTiers[$tierKey]['sgst_amount'] += $lineSgst;
                    $modTaxTiers[$tierKey]['igst_amount'] += $lineIgst;

                    $lineTotal = round($lineBase + $lineTotalTax, 2);

                    $modTotalBase += $lineBase;
                    $modTotalSgst += $lineSgst;
                    $modTotalCgst += $lineCgst;
                    $modTotalIgst += $lineIgst;
                } else {
                    $unitBase = 0.0;
                    $lineBase = 0.0;
                    $lineSgst = 0.0;
                    $lineCgst = 0.0;
                    $lineIgst = 0.0;
                    $lineTotal = 0.0;
                }

                $calculatedModItems[] = array_merge($mc, [
                    'unit_base' => $unitBase,
                    'line_base' => $lineBase,
                    'sgst_percent' => $sgstPercent,
                    'cgst_percent' => $cgstPercent,
                    'total_gst_percent' => $totalGstPercent,
                    'line_sgst' => $lineSgst,
                    'line_cgst' => $lineCgst,
                    'line_igst' => $lineIgst,
                    'line_total' => $lineTotal
                ]);
            }
        }

        ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice <?= htmlspecialchars($invoiceNum) ?></title>
<style>
  @page { margin: 28px 26px; }
  body { font-family: 'DejaVu Sans', sans-serif; color: #111827; background: #fff; font-size: 9.5px; margin: 0; padding: 0; line-height: 1.35; }
  * { box-sizing: border-box; }
  table { width: 100%; border-collapse: collapse; }
  td, th { vertical-align: top; }
  
  .header-main { margin-bottom: 14px; }
  .logo-box { background-color: #1e293b; color: #fff; width: 56px; height: 56px; text-align: center; font-weight: bold; font-size: 14px; line-height: 56px; border-radius: 8px; }
  .brand-title { font-size: 19px; font-weight: bold; color: #0f172a; margin: 0; padding: 0; line-height: 1.1; }
  .brand-sub { font-size: 8px; color: #64748b; font-weight: 700; letter-spacing: 0.5px; margin-top: 2px; }
  .invoice-text { font-size: 24px; font-weight: 900; color: #1e293b; text-align: right; letter-spacing: 1px; }

  .meta-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 7px 10px; }

  .items-table { width: 100%; margin-bottom: 12px; border: 1px solid #cbd5e1; border-collapse: collapse; }
  .items-table th { background-color: #1e293b; color: #ffffff; font-size: 8px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; padding: 4px 5px; text-align: left; vertical-align: middle; border: 1px solid #334155; }
  .items-table td { padding: 4px 5px; font-size: 8.5px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #f1f5f9; vertical-align: middle; }
  .items-table th:last-child, .items-table td:last-child { border-right: none; }
  .items-table tbody tr:nth-child(even) { background-color: #f8fafc; }
  
  .change-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }

  .bank-table { width: 100%; border: 1px solid #334155; border-collapse: collapse; }
  .bank-table th { background-color: #1e293b; color: #ffffff; font-size: 8px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; padding: 3.5px 6px; text-align: left; border: 1px solid #334155; }
  .bank-table td { padding: 3px 6px; font-size: 7.5px; border: 1px solid #334155; vertical-align: middle; }
  .bank-table td.bank-label { width: 36%; font-weight: 700; color: #1e293b; background-color: #f8fafc; text-transform: uppercase; font-size: 7.2px; }
  .bank-table td.bank-val { font-weight: 800; color: #0f172a; }

  .totals-table { width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; border-collapse: collapse; }
  .totals-table td { padding: 3.5px 7px; font-size: 8.5px; border-bottom: 1px solid #e2e8f0; }
  .totals-table td:first-child { font-weight: 600; color: #475569; border-right: 1px solid #e2e8f0; width: 55%; }
  .totals-table td:last-child { text-align: right; font-weight: 700; color: #0f172a; }
  .totals-table tr.tax-row td { background-color: #f8fafc; color: #475569; }
  .totals-table tr.grand-total td { background-color: #1e293b; font-weight: 900; font-size: 10px; color: #ffffff; }

  .terms-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; font-size: 6.5px; color: #334155; line-height: 1.25; }
  .terms-box h4 { margin: 0 0 3px 0; font-size: 8px; text-transform: uppercase; color: #0f172a; font-weight: 800; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; }
  .terms-box .terms-intro { font-size: 6.5px; color: #475569; margin: 0 0 3px 0; line-height: 1.25; }
  .terms-box .term-item { margin-bottom: 2px; }
  .terms-box .term-item strong { color: #0f172a; font-weight: 700; }
  .terms-box ol { margin: 0; padding-left: 10px; }
  .terms-box li { margin-bottom: 1px; }
  .terms-box li strong { color: #0f172a; font-weight: 700; }
  .terms-sublist { margin: 1px 0 2px 0; padding-left: 0; list-style: none; }
  .terms-sublist div { margin-bottom: 1px; }

  .declaration-box { margin-top: 5px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 7px; }
  .declaration-box h4 { margin: 0 0 2px 0; font-size: 7.5px; text-transform: uppercase; color: #0f172a; font-weight: 800; letter-spacing: 0.5px; }
  .declaration-box p { margin: 0; font-size: 6.5px; color: #475569; line-height: 1.25; }

  .seal-box { text-align: center; padding: 4px 0; }
  .seal-box .seal-company { font-size: 8.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
  .seal-box .seal-space { height: 26px; }
  .seal-box .seal-sign { font-size: 7.5px; font-weight: 700; color: #334155; }

  .footer { border-top: 1px solid #e2e8f0; padding-top: 5px; font-size: 8px; color: #64748b; display: table; width: 100%; margin-top: 8px; }
  .footer-left { display: table-cell; text-align: left; }
  .footer-right { display: table-cell; text-align: right; }
  
  .mod-banner { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 8px; margin-bottom: 10px; }
  .mod-banner h3 { font-size: 9px; font-weight: bold; color: #1e293b; margin: 0 0 2px 0; }
  .mod-banner p { font-size: 8px; color: #475569; margin: 0; }
</style>
</head>
<body>

  <!-- Header (3 Columns in order: Brand Detail | Order Detail | Invoice Detail) -->
  <table class="header-main" style="width: 100%; border-collapse: collapse; margin-bottom: 14px;">
    <tr>
      <!-- Column 1: Brand Detail -->
      <td style="width: 48%; vertical-align: top; padding-right: 12px; border-right: 1px solid #e2e8f0;">
        <table style="width: 100%; border: none;">
          <tr>
            <td style="width: 110px; text-align: left; vertical-align: top;">
              <?php if (!empty($cmsLogo)): ?>
                <div style="width: 100px; text-align: left;">
                  <img src="<?= htmlspecialchars($cmsLogo) ?>" alt="Store Logo" style="width: 100%; max-width: 88px; height: auto; vertical-align: middle;">
                </div>
              <?php else: ?>
                <div class="logo-box" style="width: 100px;">GRM</div>
              <?php endif; ?>
            </td>
            <td style="vertical-align: top; padding-left: 6px;">
              <div class="brand-title">GRM Garments</div>
              <div class="brand-sub">B2B WHOLESALE &bull; GARMENTS</div>
              <div style="font-size: 8px; color: #475569; margin-top: 3px; line-height: 1.35;">
                7/340A, Rajalinga Nagar, Kadampadi PO,<br>
                Sulur, Coimbatore, Tamil Nadu - 641401<br>
                grmb2bsupport@gmail.com &nbsp;|&nbsp; +91 9944323726<br>
                GSTIN: <strong style="color: #0f172a;"><?= htmlspecialchars($cmsGst) ?></strong>
              </div>
            </td>
          </tr>
        </table>
      </td>

      <!-- Column 2: Order Detail -->
      <td style="width: 26%; vertical-align: middle; padding: 0 10px; border-right: 1px solid #e2e8f0;">
        <table style="width: 100%; border: none; font-size: 8.5px; line-height: 1.5;">
          <tr>
            <td style="font-weight: 700; color: #64748b; width: 35%; border: none; padding: 1.5px 0;">Order #</td>
            <td style="font-weight: 800; color: #0f172a; border: none; padding: 1.5px 0;"><?= htmlspecialchars($order['order_number']) ?></td>
          </tr>
          <tr>
            <td style="font-weight: 700; color: #64748b; border: none; padding: 1.5px 0;">Placed</td>
            <td style="font-weight: 700; color: #334155; border: none; padding: 1.5px 0; white-space: nowrap;"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
          </tr>
          <tr>
            <td style="font-weight: 700; color: #64748b; border: none; padding: 2px 0 1px 0;">Status</td>
            <td style="border: none; padding: 2px 0 1px 0;">
              <span style="color: #059669; font-weight: 800; padding: 1.5px 6px; background-color: #d1fae5; border-radius: 4px; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
              </span>
            </td>
          </tr>
        </table>
      </td>

      <!-- Column 3: Invoice Detail -->
      <td style="width: 26%; vertical-align: middle; text-align: center; padding-left: 12px;">
        <div style="font-size: 22px; font-weight: 900; color: #1e293b; letter-spacing: 1.5px; line-height: 1.1;">INVOICE</div>
        <div style="font-size: 10px; font-weight: 800; color: #334155; margin-top: 4px; letter-spacing: 0.5px;"><?= htmlspecialchars($invoiceNum) ?></div>
        <?php if ($hasModification): ?>
          <div style="color: #ef4444; font-size: 7.5px; font-weight: 800; margin-top: 2px;">MODIFIED ORDER</div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- Buyer & Shipping Info -->
  <div style="width: 100%; margin-bottom: 14px;">
    <div class="meta-card">
      <table style="width: 100%; border: none;">
        <tr>
          <!-- Buyer Details Column -->
          <td style="width: 50%; vertical-align: top; padding-right: 12px; border-right: 1px solid #e2e8f0;">
            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 3px;">Billed To (Buyer Details)</div>
            
            <div style="font-size: 10.5px; color: #0f172a; margin-bottom: 3px; line-height: 1.25;">
              <strong><?= htmlspecialchars($order['buyer_name'] ?? '') ?></strong>
              <?php if (!empty($order['business_name'])): ?>
                <br><span style="color: #334155; font-weight: 700; font-size: 9px;"><?= htmlspecialchars($order['business_name']) ?></span>
              <?php endif; ?>
            </div>

            <div style="font-size: 8px; color: #475569; line-height: 1.4;">
              <?php if (!empty($order['buyer_gst_number'])): ?>
                <strong>GSTIN:</strong> <span style="color: #0f172a; font-weight: 800; font-family: monospace; font-size: 8.5px;"><?= htmlspecialchars($order['buyer_gst_number']) ?></span><br>
              <?php endif; ?>
              <strong>Phone:</strong> <?= htmlspecialchars($order['buyer_phone'] ?? 'N/A') ?><br>
              <strong>Email:</strong> <?= htmlspecialchars($order['buyer_email'] ?? 'N/A') ?>
            </div>
          </td>

          <!-- Shipping Details Column -->
          <td style="width: 50%; vertical-align: top; padding-left: 12px;">
            <div style="font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 3px;">Shipped / Delivered To</div>
            <div style="font-size: 8.5px; color: #334155; line-height: 1.4;">
              <?php 
                $shipName = $order['buyer_name'] ?? ($address['name'] ?? 'Customer');
                $shipPhone = $order['buyer_phone'] ?? ($address['phone'] ?? '');
              ?>
              <strong><?= htmlspecialchars($shipName) ?></strong>
              <?php if (!empty($shipPhone)): ?> &bull; <?= htmlspecialchars($shipPhone) ?><?php endif; ?><br>
              <?php if (!empty($order['shipping_address'])): ?>
                <?= self::formatCleanAddress($order['shipping_address']) ?>
              <?php elseif (!empty($address)): 
                $addrL1 = $address['line1'] ?? $address['address_line1'] ?? '';
                $addrL2 = $address['line2'] ?? $address['address_line2'] ?? '';
                $addrCity = $address['city'] ?? '';
                $addrState = $address['state'] ?? '';
                $addrPin = $address['postal_code'] ?? $address['pincode'] ?? '';
              ?>
                <?php if (!empty($addrL1)): ?><?= htmlspecialchars($addrL1) ?><br><?php endif; ?>
                <?php if (!empty($addrL2)): ?><?= htmlspecialchars($addrL2) ?><br><?php endif; ?>
                <?= htmlspecialchars($addrCity) ?><?= (!empty($addrCity) && !empty($addrState)) ? ', ' : '' ?><?= htmlspecialchars($addrState) ?><?= !empty($addrPin) ? ' - ' . htmlspecialchars($addrPin) : '' ?>
              <?php else: ?>
                <span style="color: #94a3b8; font-style: italic;">No specific shipping address on file.</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <?php if ($hasModification): ?>
    <div class="mod-banner">
      <h3>Order Modification Applied (#<?= $modification['id'] ?>)</h3>
      <p>This order has been revised. The table below highlights items that were added, removed, or updated in quantity/price.</p>
    </div>
  <?php endif; ?>

  <!-- Items Table with dedicated HSN, single Rate %, CGST, SGST and IGST Amount columns -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width: 3%; text-align: center;">#</th>
        <th style="width: <?= $hasModification ? '16.5%' : '20%' ?>;">PRODUCT</th>
        <th style="width: <?= $hasModification ? '6.5%' : '7%' ?>; text-align: center;">HSN / SAC</th>
        <?php if ($hasModification): ?>
          <th style="width: 7%; text-align: center;">Status</th>
        <?php endif; ?>
        <th style="width: 4.5%; text-align: center;">Qty</th>
        <th style="width: <?= $hasModification ? '10%' : '11%' ?>; text-align: right;">Base Price (Per Piece)</th>
        <th style="width: <?= $hasModification ? '10%' : '11%' ?>; text-align: right;">Qty * Base Price (₹)</th>
        <th style="width: <?= $hasModification ? '5%' : '5.5%' ?>; text-align: center;">Rate %</th>
        <th style="width: <?= $hasModification ? '8.5%' : '9%' ?>; text-align: right;">CGST (₹)</th>
        <th style="width: <?= $hasModification ? '8.5%' : '9%' ?>; text-align: right;">SGST (₹)</th>
        <th style="width: <?= $hasModification ? '8.5%' : '9%' ?>; text-align: right;">IGST (₹)</th>
        <th style="width: <?= $hasModification ? '12%' : '11%' ?>; text-align: right;">Total (₹)</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($hasModification && !empty($calculatedModItems)): ?>
        <?php foreach ($calculatedModItems as $idx => $item): 
          $ct = $item['change_type'];
          $isRemoved = $ct === 'removed';
          $combGst = (float)($item['total_gst_percent'] ?? 0);
          if ($combGst <= 0) {
              $combGst = (float)($item['cgst_percent'] ?? 0) + (float)($item['sgst_percent'] ?? 0);
          }
          $rateDisplay = $combGst > 0 ? ($combGst . '%') : '0%';
        ?>
          <tr style="<?= $isRemoved ? 'background-color: #fff1f2;' : '' ?>">
            <td style="text-align: center; color: #64748b;"><?= $idx + 1 ?></td>
            <td>
              <strong style="color: #0f172a; <?= $isRemoved ? 'text-decoration: line-through; color: #991b1b;' : '' ?>"><?= htmlspecialchars($item['product_name']) ?></strong>
              <?php if (!empty($item['variant_name'])): ?>
                <div style="font-size: 7.5px; color: #64748b;"><?= htmlspecialchars($item['variant_name']) ?></div>
              <?php endif; ?>
              <?php if (!empty($item['note'])): ?>
                <div style="font-size: 7px; color: #94a3b8; font-style: italic;">Note: <?= htmlspecialchars($item['note']) ?></div>
              <?php endif; ?>
            </td>
            <td style="text-align: center; font-family: monospace; font-size: 8px; font-weight: 700; color: #334155;">
              <?= !empty($item['category_hsn_code']) ? htmlspecialchars($item['category_hsn_code']) : '-' ?>
            </td>
            <td>
              <span class="change-badge" style="background: <?= $changeTypeBg[$ct] ?>; color: <?= $changeTypeColor[$ct] ?>;">
                <?= $changeTypeLabel[$ct] ?? ucfirst($ct) ?>
              </span>
            </td>
            <td style="text-align: center; font-weight: 700; <?= $isRemoved ? 'text-decoration: line-through;' : '' ?>">
              <?php if ($ct === 'qty_changed'): ?>
                <span style="color: #94a3b8; font-size: 7.5px;"><?= $item['old_qty'] ?> &rarr; </span><strong><?= $item['new_qty'] ?></strong>
              <?php else: ?>
                <?= $isRemoved ? $item['old_qty'] : $item['new_qty'] ?>
              <?php endif; ?>
            </td>
            <td style="text-align: right;">
              <?= $isRemoved ? '-' : '₹' . number_format($item['unit_base'], 2) ?>
            </td>
            <td style="text-align: right;">
              <?= $isRemoved ? '-' : '₹' . number_format($item['line_base'], 2) ?>
            </td>
            <td style="text-align: center; color: #475569; font-size: 8px;">
              <?= $isRemoved ? '-' : $rateDisplay ?>
            </td>
            <td style="text-align: right; color: #d8407d;">
              <?= $isRemoved ? '-' : '₹' . number_format($item['line_cgst'], 2) ?>
            </td>
            <td style="text-align: right; color: #d8407d;">
              <?= $isRemoved ? '-' : '₹' . number_format($item['line_sgst'], 2) ?>
            </td>
            <td style="text-align: right; color: #d8407d;">
              <?= $isRemoved ? '-' : '₹' . number_format($item['line_igst'], 2) ?>
            </td>
            <td style="text-align: right; font-weight: 800; <?= $isRemoved ? 'color: #991b1b; text-decoration: line-through;' : 'color: #0f172a;' ?>">
              <?= $isRemoved ? '₹0.00' : '₹' . number_format($item['line_total'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($calculatedItems as $idx => $item): 
          $combGst = (float)($item['total_gst_percent'] ?? 0);
          if ($combGst <= 0) {
              $combGst = (float)($item['cgst_percent'] ?? 0) + (float)($item['sgst_percent'] ?? 0);
          }
          $rateDisplay = $combGst > 0 ? ($combGst . '%') : '0%';
        ?>
          <tr>
            <td style="text-align: center; color: #64748b;"><?= $idx + 1 ?></td>
            <td>
              <strong style="color: #0f172a;"><?= htmlspecialchars($item['product_name']) ?></strong>
              <?php if (!empty($item['variant_name'])): ?>
                <div style="font-size: 7.5px; color: #64748b;"><?= htmlspecialchars($item['variant_name']) ?></div>
              <?php endif; ?>
            </td>
            <td style="text-align: center; font-family: monospace; font-size: 8px; font-weight: 700; color: #334155;">
              <?= !empty($item['category_hsn_code']) ? htmlspecialchars($item['category_hsn_code']) : '-' ?>
            </td>
            <td style="text-align: center; font-weight: 700;"><?= $item['quantity'] ?></td>
            <td style="text-align: right;">₹<?= number_format($item['unit_base'], 2) ?></td>
            <td style="text-align: right;">₹<?= number_format($item['line_base'], 2) ?></td>
            <td style="text-align: center; color: #475569; font-size: 8px;">
              <?= $rateDisplay ?>
            </td>
            <td style="text-align: right; color: #475569;">
              ₹<?= number_format($item['line_cgst'], 2) ?>
            </td>
            <td style="text-align: right; color: #475569;">
              ₹<?= number_format($item['line_sgst'], 2) ?>
            </td>
            <td style="text-align: right; color: #475569;">
              ₹<?= number_format($item['line_igst'], 2) ?>
            </td>
            <td style="text-align: right; font-weight: 800; color: #0f172a;">₹<?= number_format($item['line_total'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Calculation & Breakdown Totals -->
  <?php
    $displayBase = $hasModification ? $modTotalBase : $totalBase;
    $displaySgst = $hasModification ? $modTotalSgst : $totalSgst;
    $displayCgst = $hasModification ? $modTotalCgst : $totalCgst;
    $displayIgst = $hasModification ? $modTotalIgst : $totalIgst;
    $displayItemsTotal = $displayBase + $displaySgst + $displayCgst + $displayIgst;

    $rawTaxTiers = $hasModification ? $modTaxTiers : $taxTiers;
    $displayTaxTiers = $rawTaxTiers;
    uasort($displayTaxTiers, function($a, $b) {
        return (float)$a['total_rate'] <=> (float)$b['total_rate'];
    });

    if (empty($displayTaxTiers)) {
        $displayTaxTiers = [
            '0%' => [
                'total_rate' => 0.0,
                'cgst_rate' => 0.0,
                'sgst_rate' => 0.0,
                'igst_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
            ]
        ];
    }

    $shippingFee = (float)($order['shipping_fee'] ?? 0);
    $weightFee = (float)($order['weight_fee'] ?? 0);
    $platformFee = (float)($order['platform_fee'] ?? 0);
    $packagingFee = (float)($order['packaging_fee'] ?? 0);
    
    $storedGrandTotal = $hasModification ? (float)$modification['revised_total'] : (float)($order['grand_total'] ?? $order['total_amount']);
    $unroundedTotal = $displayItemsTotal + $shippingFee + $platformFee + $packagingFee + $weightFee;

    if ($storedGrandTotal > 0 && abs($unroundedTotal - $storedGrandTotal) < 0.05) {
        $finalGrandTotal = $storedGrandTotal;
        $roundOff = 0.0;
    } else {
        $finalGrandTotal = (float)round($unroundedTotal);
        $roundOff = round($finalGrandTotal - $unroundedTotal, 2);
        if (abs($roundOff) < 0.001) {
            $roundOff = 0.0;
        }
    }
  ?>

  <!-- Bottom Section: Bank Account Details and Grand Total in a Single Row -->
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
    <tr>
      <!-- Left Column: Bank Account Details -->
      <td style="width: 53%; vertical-align: top; padding-right: 10px;">
        <table class="bank-table">
          <tr>
            <th colspan="2">BANK ACCOUNT DETAILS</th>
          </tr>
          <tr>
            <td class="bank-label">ACCOUNT NAME</td>
            <td class="bank-val">GRM GARMENTS</td>
          </tr>
          <tr>
            <td class="bank-label">BANK NAME</td>
            <td class="bank-val">UCO BANK</td>
          </tr>
          <tr>
            <td class="bank-label">ACCOUNT NO</td>
            <td class="bank-val" style="font-family: monospace; font-size: 8.5px; letter-spacing: 0.5px;">34890210000830</td>
          </tr>
          <tr>
            <td class="bank-label">IFSC CODE</td>
            <td class="bank-val" style="font-family: monospace; font-size: 8.5px; letter-spacing: 0.5px;">UCBA0003489</td>
          </tr>
          <tr>
            <td class="bank-label">BRANCH NAME</td>
            <td class="bank-val">SULUR (CODE: 3489)</td>
          </tr>
          <tr>
            <td class="bank-label">BRANCH ADDRESS</td>
            <td class="bank-val" style="font-size: 7.2px; line-height: 1.25;">1323, Trichy Main Road, Near Registry Office, Sulur, Coimbatore, TN - 641041</td>
          </tr>
        </table>
      </td>

      <!-- Right Column: Final Totals Summary (Grand Total) & Signatory Seal -->
      <td style="width: 47%; vertical-align: top; padding-left: 10px;">
        <table class="totals-table">
          <tr>
            <td>Items Subtotal</td>
            <td>₹<?= number_format($displayBase, 2) ?></td>
          </tr>
          <?php if ($isTamilNadu): ?>
            <?php foreach ($displayTaxTiers as $tier): ?>
              <tr class="tax-row">
                <td>Total CGST (<?= (float)$tier['cgst_rate'] ?>%)</td>
                <td>₹<?= number_format($tier['cgst_amount'], 2) ?></td>
              </tr>
              <tr class="tax-row">
                <td>Total SGST (<?= (float)$tier['sgst_rate'] ?>%)</td>
                <td>₹<?= number_format($tier['sgst_amount'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="tax-row">
              <td>Total IGST (0%)</td>
              <td>₹0.00</td>
            </tr>
          <?php else: ?>
            <tr class="tax-row">
              <td>Total CGST (0%)</td>
              <td>₹0.00</td>
            </tr>
            <tr class="tax-row">
              <td>Total SGST (0%)</td>
              <td>₹0.00</td>
            </tr>
            <?php foreach ($displayTaxTiers as $tier): ?>
              <tr class="tax-row">
                <td>Total IGST (<?= (float)$tier['igst_rate'] ?>%)</td>
                <td>₹<?= number_format($tier['igst_amount'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if ($shippingFee > 0): ?>
            <tr>
              <td>Shipping Charges</td>
              <td>₹<?= number_format($shippingFee, 2) ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($weightFee > 0): ?>
            <tr>
              <td>Weight Fee</td>
              <td>₹<?= number_format($weightFee, 2) ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($platformFee > 0): ?>
            <tr>
              <td>Platform / Service Fee</td>
              <td>₹<?= number_format($platformFee, 2) ?></td>
            </tr>
          <?php endif; ?>
          <?php if ($packagingFee > 0): ?>
            <tr>
              <td>Packaging Fee</td>
              <td>₹<?= number_format($packagingFee, 2) ?></td>
            </tr>
          <?php endif; ?>
          <tr>
            <td>Round Off</td>
            <td>
              <?php if ($roundOff > 0): ?>
                + ₹<?= number_format($roundOff, 2) ?>
              <?php else: ?>
                ₹0.00
              <?php endif; ?>
            </td>
          </tr>
          <tr class="grand-total">
            <td style="color: #ffffff; border-right: 1px solid #3b82f6;">GRAND TOTAL</td>
            <td style="color: #ffffff;">₹<?= number_format($finalGrandTotal, 2) ?></td>
          </tr>
        </table>

        <!-- Official Signatory & Seal (Right Bottom) -->
        <div class="seal-box" style="margin-top: 10px;">
          <div class="seal-company">FOR GRM GARMENTS</div>
          <div class="seal-space"></div>
          <div class="seal-sign">Authorized Signatory &amp; Seal</div>
        </div>
      </td>
    </tr>
  </table>

  <!-- Page 1 Footer -->
  <div class="footer">
    <div class="footer-left">
      <strong>GRM Garments B2B Wholesale</strong> &bull; Tax Invoice
    </div>
    <div class="footer-right">
      Phone / WhatsApp: +91 9944323726 &bull; Computer-Generated Invoice
    </div>
  </div>

  <!-- Page Break to Next Page for Terms & Conditions -->
  <div style="page-break-before: always;"></div>

  <!-- Page 2: Full Terms & Conditions and Declaration -->
  <div class="terms-box" style="margin-top: 6px;">
    <h4>GRM B2B Wholesale Terms &amp; Conditions</h4>
    <p class="terms-intro">These Terms and Conditions (&ldquo;Terms&rdquo;) govern every wholesale order placed with the Company (&ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;) by a registered business buyer (&ldquo;the Buyer,&rdquo; &ldquo;you,&rdquo; or &ldquo;the Customer&rdquo;). They are written to set out, in clear and professional language, exactly what both sides can expect once an order is placed. By submitting a purchase order, confirming a quotation or invoice, or otherwise placing a wholesale order with us &mdash; whether by phone, email, in person, or through our website &mdash; the Buyer acknowledges that it has read, understood, and agreed to be bound by these Terms in full.</p>

    <div class="terms-list">
      <div class="term-item"><strong>1. Definitions:</strong> 1.1 &ldquo;Buyer&rdquo; means the business placing an Order with the Company. 1.2 &ldquo;Order&rdquo; means a purchase order, accepted quotation, or any request to buy goods, submitted through any channel. 1.3 &ldquo;Order Confirmation&rdquo; means the Company's written confirmation, proforma invoice, or tax invoice for an Order. 1.4 &ldquo;Goods&rdquo; means the products supplied under an Order. 1.5 &ldquo;Assorted&rdquo; means Goods sold in mixed or random designs, colours, or prints selected by the Company.</div>
      <div class="term-item"><strong>2. Wholesale Only:</strong> 2.1 The Company sells only to businesses buying for resale, distribution, or commercial use, and does not sell to individual retail consumers. 2.2 The Buyer must provide a valid GSTIN or other business registration proof [if applicable] before its first Order. 2.3 The minimum order quantity is [quantity/value], unless stated otherwise in the quotation. 2.4 The Company may decline or limit any Order, or request further verification, at its discretion. 2.5 Wholesale prices and catalogues are for trade Buyers only and must not be quoted to end consumers.</div>
      <div class="term-item"><strong>3. Orders and Confirmation:</strong> 3.1 An Order becomes binding once the Company issues an Order Confirmation and, where full payment is required before dispatch under Section 4, once that payment is received. 3.2 The Buyer must check the product, design, quantity, size, and price on the Order Confirmation and report any error within [24] hours of receiving it. 3.3 The Company will not substitute products or reduce quantities without the Buyer's consent, except for Assorted Goods under Section 5. 3.4 If an item becomes unavailable after confirmation, the Company will inform the Buyer before dispatch. The Buyer may choose a partial shipment, a back-order, or an agreed substitute; any amount paid for items not supplied will be refunded or credited within [3] working days.</div>
      <div class="term-item"><strong>4. Prices and Payment:</strong> 4.1 Prices are in Rupees and exclusive of GST, freight, and insurance, unless the quotation states otherwise. 4.2 Quotations and proforma invoices are valid for 48 hours. If full payment is not received within that period, the quotation lapses and prices may be revised before a new one is issued. 4.3 Full payment is required before dispatch. The Company does not offer credit or cash on delivery (COD). 4.4 Payments must be made only to the Company's official bank account; the Company is not responsible for payments made to any other account. 4.5 Ownership of the Goods passes to the Buyer once full payment is received and the Goods are dispatched.</div>
      <div class="term-item"><strong>5. Product Images, Assorted Designs, and Variations:</strong> 5.1 Images on the Company's website, catalogues, and marketing materials are for reference only; actual design, print placement, colour, pattern, texture, finish, and packaging may vary. 5.2 For Assorted Goods, designs, colours, and prints are selected by the Company at the time of packing and may differ from the images shown. A Buyer wanting a specific design must request it in writing before confirmation; this is subject to stock availability and may affect price or lead time. 5.3 The following are normal variations and not defects: colour or shade differences between production batches; minor variation in size, weight, or dimensions within [&plusmn;5]%; colour differences caused by screen display or photography; and the random selection of designs in Assorted Goods. 5.4 None of the variations in this Section is grounds for rejection, return, or refund.</div>
      <div class="term-item"><strong>6. Dispatch, Delivery, and Transit Risk:</strong> 6.1 Goods are usually dispatched within [3] working days of full payment being received. Dispatch and delivery dates are estimates only; a delay does not entitle the Buyer to cancel the Order or claim compensation. 6.2 Goods are sent through [the Company's chosen transporter / a transporter nominated by the Buyer]; freight is [payable by the Buyer / included in the price, as stated in the quotation]. 6.3 Risk of loss or damage passes to the Buyer once the Goods are handed to the transporter, or collected by the Buyer from the Company's premises. 6.4 Loss or damage in transit must be claimed from the transporter or insurer. The Company will share the relevant dispatch documents and reasonably support the claim.</div>
      <div class="term-item"><strong>7. Inspection and Claims:</strong> 7.1 At delivery, the Buyer must check the number of cartons or packages against the delivery documents and confirm the outer packaging is intact. Any missing item or visible damage must be noted on the transporter's delivery receipt before signing. 7.2 Within [48] hours of delivery, the Buyer must inspect the contents. Any shortage, wrong item, or manufacturing defect must be reported to [support contact] along with the invoice number, clear photographs, and, where possible, an unedited video of the sealed package being opened. 7.3 The Company will not accept a claim that is reported after this window, made without the evidence above, concerning Goods that have been used, altered, repacked, or sold on, or concerning any variation described in Section 5. 7.4 Where a claim is valid, the Company will, at its choice, replace the affected items, supply the missing items, or issue a credit note for their invoice value. This is the Buyer's sole remedy for shortage, wrong supply, or defects.</div>
      <div class="term-item"><strong>8. No Returns, Exchanges, or Refunds &ndash; Strict Policy:</strong> 8.1 No Goods may be sent back to the Company without its prior written approval; Goods returned without approval will be refused or held at the Buyer's cost. 8.2 Except for valid claims under Section 7 and refunds for unsupplied items under Section 3.4, the Company does not accept returns, exchanges, or refunds once an Order is confirmed. 8.3 This applies regardless of the reason given, including change of preference or the variations described in Section 5.</div>
      <div class="term-item"><strong>9. Cancellation and Changes:</strong> 9.1 The Buyer cannot cancel an Order once it is binding under Section 3.1. 9.2 A request to increase quantity or change specification may be accepted at the Company's discretion before dispatch; any additional amount must be paid in full before dispatch, and no refund is given for a reduction or change. 9.3 If the Buyer refuses to accept delivery of paid Goods, no refund will be given, and the Buyer must bear the return freight and any storage charges. Goods not collected within [30] days of written notice may be disposed of or resold by the Company. 9.4 The Company may cancel an Order it cannot supply for reasons beyond its control; in that case, any amount paid for the unsupplied Goods will be refunded.</div>
      <div class="term-item"><strong>10. Limitation of Liability:</strong> 10.1 The Company's total liability for any Order is limited to the invoice value of the Goods concerned. 10.2 The Company is not liable for loss of profit, loss of business, or any indirect or consequential loss. 10.3 The Company's liability for the quality of Goods is limited to the remedies in Section 7; to the extent permitted by law, all other conditions and warranties about quality or fitness for a particular purpose are excluded.</div>
      <div class="term-item"><strong>11. Force Majeure:</strong> The Company is not liable for any delay or failure to supply caused by events beyond its reasonable control, including natural disasters, fire, epidemics, strikes, power failure, shortage of raw materials, transport disruption, or government action. If such an event continues for more than [30] days, either party may cancel the unsupplied part of the Order, and any amount paid for it will be refunded.</div>
      <div class="term-item"><strong>12. Designs and Intellectual Property:</strong> 12.1 All images, catalogues, designs, and content on the Company's website remain its property and may not be copied or used without written permission. 12.2 Where the Buyer supplies its own design, logo, or artwork for use on the Goods or packaging, the Buyer confirms it owns or has the right to use it, and will indemnify the Company against any claim that it infringes another person's rights.</div>
      <div class="term-item"><strong>13. Privacy:</strong> The Company collects and uses the Buyer's business and contact details only to process Orders, deliver Goods, and communicate about its products. Further details are set out in the Company's Privacy Policy.</div>
      <div class="term-item"><strong>14. General:</strong> 14.1 By placing an Order through any channel, the Buyer agrees to these Terms, which apply to every Order even if not separately referenced on the purchase order, invoice, or delivery note. 14.2 These Terms override any terms in the Buyer's purchase order, unless an authorised representative of the Company agrees otherwise in writing. 14.3 The Company may update these Terms at any time; the version in effect at the time an Order is confirmed applies to that Order. 14.4 If any clause is found invalid or unenforceable, the remaining clauses continue to apply. 14.5 If the Company does not enforce a clause at any time, it does not lose the right to enforce it later.</div>
      <div class="term-item"><strong>15. Governing Law and Disputes:</strong> 15.1 These Terms are governed by the laws of [India\ Tamil Nadu]. 15.2 The parties will first try to resolve any dispute through good-faith discussion within [15] days. 15.3 Any unresolved dispute is subject to the exclusive jurisdiction of the courts at [coimbatore], [India\ Tamil Nadu].</div>
    </div>
  </div>

  <div class="declaration-box" style="margin-top: 6px;">
    <h4>Declaration:</h4>
    <p>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct. Thanks for doing business with us.</p>
  </div>

  <!-- Page 2 Footer -->
  <div class="footer" style="margin-top: 8px;">
    <div class="footer-left">
      <strong>GRM Garments B2B Wholesale</strong> &bull; Tax Invoice
    </div>
    <div class="footer-right">
      Phone / WhatsApp: +91 9944323726 &bull; Terms &amp; Conditions
    </div>
  </div>

</body>
</html>
<?php
        return ob_get_clean();
    }
}
