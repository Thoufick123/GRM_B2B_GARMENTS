<?php

namespace App\Services;

use PDO;

class RegistrationAddressService
{
    private const STATES = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
        'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
        'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
        'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry',
    ];

    public static function ensureShopAddress(PDO $db, int $userId, string $shopLocation): void
    {
        $parsed = self::parseShopLocation($shopLocation);
        if ($parsed === null) {
            return;
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
        $stmt->execute([$userId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $db->prepare("
                INSERT INTO addresses (user_id, label, line1, city, state, postal_code, country, is_default)
                VALUES (?, 'Shop Address', ?, ?, ?, ?, 'India', 1)
            ")->execute([$userId, $parsed['line1'], $parsed['city'], $parsed['state'], $parsed['postal_code']]);
            return;
        }

        $stmt = $db->prepare("
            SELECT id, line1, city, state, postal_code
            FROM addresses
            WHERE user_id = ? AND label = 'Shop Address'
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$address) {
            return;
        }

        $legacyParts = self::parseWithLegacyRule($shopLocation);
        if ($legacyParts === null
            || trim((string)$address['line1']) !== $legacyParts['line1']
            || trim((string)$address['city']) !== $legacyParts['city']
            || trim((string)$address['state']) !== $legacyParts['state']
            || trim((string)$address['postal_code']) !== $legacyParts['postal_code']) {
            return;
        }

        $db->prepare("
            UPDATE addresses
            SET line1 = ?, city = ?, state = ?, postal_code = ?
            WHERE id = ? AND user_id = ?
        ")->execute([
            $parsed['line1'],
            $parsed['city'],
            $parsed['state'],
            $parsed['postal_code'],
            $address['id'],
            $userId,
        ]);
    }

    public static function parseShopLocation(string $shopLocation): ?array
    {
        $location = trim($shopLocation);
        if (!preg_match('/^(.*)\s*-\s*(\d{6})$/', $location, $matches)) {
            return null;
        }

        $addressAndRegion = trim($matches[1]);
        $postalCode = $matches[2];
        $states = self::STATES;
        usort($states, static function (string $a, string $b): int {
            return strlen($b) <=> strlen($a);
        });

        foreach ($states as $state) {
            $suffix = ', ' . $state;
            if (strcasecmp(substr($addressAndRegion, -strlen($suffix)), $suffix) !== 0) {
                continue;
            }

            $addressAndCity = trim(substr($addressAndRegion, 0, -strlen($suffix)));
            $citySeparator = strrpos($addressAndCity, ',');
            if ($citySeparator === false) {
                return null;
            }

            $line1 = trim(substr($addressAndCity, 0, $citySeparator));
            $city = trim(substr($addressAndCity, $citySeparator + 1));
            if ($line1 === '' || $city === '') {
                return null;
            }

            return [
                'line1' => $line1,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postalCode,
            ];
        }

        return null;
    }

    private static function parseWithLegacyRule(string $shopLocation): ?array
    {
        if (!preg_match('/^(.*?),\s*(.*?),\s*(.*?)\s*-\s*(\d{6})$/', trim($shopLocation), $matches)) {
            return null;
        }

        return [
            'line1' => trim($matches[1]),
            'city' => trim($matches[2]),
            'state' => trim($matches[3]),
            'postal_code' => $matches[4],
        ];
    }
}
