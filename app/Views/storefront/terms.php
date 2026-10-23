<div class="min-h-screen bg-slate-50/60 pb-16">

    <!-- â”€â”€ Breadcrumb Section â”€â”€ -->
    <div class="bg-white border-b border-gray-200/80">
        <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8 py-3.5">
            <nav class="flex items-center space-x-2 text-sm font-medium">
                <a href="<?= BASE_URL ?>/" class="inline-flex items-center gap-1.5 text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors">
                    <svg class="w-4 h-4 shrink-0 text-[#F25996]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                    <span>Home</span>
                </a>
                <svg class="w-4 h-4 text-[#F25996] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-[#F25996] font-bold">Terms &amp; Conditions</span>
            </nav>
        </div>
    </div>

    <!-- â”€â”€ Hero Banner Section â”€â”€ -->
    <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8 pt-8">
        <div class="relative overflow-hidden rounded-3xl border border-pink-200/70 shadow-sm p-6 sm:p-10 lg:p-12"
             style="background: linear-gradient(135deg, #fdf2f7 0%, #fce7f3 55%, #fdf4f8 100%);">
            
            <!-- Decorative background glows -->
            <div class="absolute -right-16 -top-16 w-80 h-80 bg-pink-300/30 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-pink-200/40 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-12">
                
                <!-- Left Content -->
                <div class="w-full lg:w-3/5 space-y-3">
                    <div class="inline-block">
                        <span class="text-xs sm:text-sm font-extrabold tracking-widest uppercase text-[#F25996] bg-pink-100/80 px-3 py-1 rounded-full border border-pink-200/60">
                            OUR POLICIES
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#0f2963] font-display tracking-tight leading-tight">
                        GRM B2B Wholesale Terms &amp; <span class="text-[#F25996]">Conditions</span>
                    </h1>

                    <p class="text-slate-600 text-base sm:text-lg max-w-xl leading-relaxed pt-1">
                        Please read our terms and conditions carefully before placing an order on GRM B2B Wholesale.
                    </p>
                </div>

                <!-- Right Visual: Document Shield + Trust Badges -->
                <div class="w-full lg:w-2/5 flex flex-col sm:flex-row items-center justify-center lg:justify-end gap-6 sm:gap-8">
                    
                    <!-- Document & Shield Graphic -->
                    <div class="relative flex-shrink-0">
                        <div class="w-28 h-36 sm:w-32 sm:h-40 bg-white rounded-2xl shadow-lg border-2 border-pink-200 flex flex-col p-3.5 justify-between relative transform -rotate-2 hover:rotate-0 transition-transform duration-300">
                            <!-- Document lines -->
                            <div class="space-y-2 pt-1">
                                <div class="h-2.5 bg-[#F25996] rounded-full w-3/4"></div>
                                <div class="h-1.5 bg-pink-200 rounded-full w-full"></div>
                                <div class="h-1.5 bg-pink-200 rounded-full w-5/6"></div>
                                <div class="h-1.5 bg-pink-200 rounded-full w-4/6"></div>
                                <div class="h-1.5 bg-pink-200 rounded-full w-full"></div>
                            </div>
                            <!-- Shield badge on bottom right of doc -->
                            <div class="absolute -bottom-3 -right-3 w-12 h-14 bg-gradient-to-br from-[#F25996] to-[#d8407d] rounded-b-xl rounded-t-sm shadow-md flex items-center justify-center border-2 border-white">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Pillars -->
                    <div class="flex flex-col space-y-3.5 relative">
                        <!-- Fair Trade -->
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-[#F25996] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-[#0f2963] text-sm sm:text-base tracking-tight">Fair Trade</span>
                        </div>

                        <!-- Trusted Partnership -->
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-[#F25996] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-[#0f2963] text-sm sm:text-base tracking-tight">Trusted Partnership</span>
                        </div>

                        <!-- Long Term Growth -->
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-[#F25996] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-[#0f2963] text-sm sm:text-base tracking-tight">Long Term Growth</span>
                        </div>

                        <!-- Decorative Orange Swoosh -->
                        <div class="pt-1">
                            <svg class="w-24 h-3 text-[#F25996]" viewBox="0 0 100 12" fill="none">
                                <path d="M2 9C25 2 75 2 98 9" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- â”€â”€ Main Terms Card Section â”€â”€ -->
    <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8 pt-8">
        <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 sm:p-10 lg:p-12">
            
            <!-- Card Header -->
            <div class="mb-8 pb-6 border-b border-gray-200/80">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-100/80 border border-pink-200/60 text-[#F25996] text-xs font-bold uppercase tracking-wider mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Official B2B Agreement</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f2963] tracking-tight font-display">
                    GRM B2B Wholesale Terms &amp; Conditions
                </h2>
                <p class="text-slate-600 text-sm sm:text-base mt-3 leading-relaxed">
                    These Terms and Conditions (&ldquo;Terms&rdquo;) govern every wholesale order placed with the Company (&ldquo;we,&rdquo; &ldquo;us,&rdquo; or &ldquo;our&rdquo;) by a registered business buyer (&ldquo;the Buyer,&rdquo; &ldquo;you,&rdquo; or &ldquo;the Customer&rdquo;). They are written to set out, in clear and professional language, exactly what both sides can expect once an order is placed. By submitting a purchase order, confirming a quotation or invoice, or otherwise placing a wholesale order with us &mdash; whether by phone, email, in person, or through our website &mdash; the Buyer acknowledges that it has read, understood, and agreed to be bound by these Terms in full.
                </p>
            </div>

            <!-- List of 15 B2B Wholesale Terms -->
            <div class="space-y-6 sm:space-y-8 divide-y divide-gray-100">

                <!-- Section 1 -->
                <div class="pt-6 first:pt-0">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            1
                        </span>
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                                Definitions
                            </h3>
                        </div>
                    </div>
                    <ul class="space-y-2 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <li><strong class="text-gray-900">1.1 &ldquo;Buyer&rdquo;</strong> means the business placing an Order with the Company.</li>
                        <li><strong class="text-gray-900">1.2 &ldquo;Order&rdquo;</strong> means a purchase order, accepted quotation, or any request to buy goods, submitted through any channel.</li>
                        <li><strong class="text-gray-900">1.3 &ldquo;Order Confirmation&rdquo;</strong> means the Company's written confirmation, proforma invoice, or tax invoice for an Order.</li>
                        <li><strong class="text-gray-900">1.4 &ldquo;Goods&rdquo;</strong> means the products supplied under an Order.</li>
                        <li><strong class="text-gray-900">1.5 &ldquo;Assorted&rdquo;</strong> means Goods sold in mixed or random designs, colours, or prints selected by the Company.</li>
                    </ul>
                </div>

                <!-- Section 2 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            2
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Wholesale Only
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">2.1</strong> The Company sells only to businesses buying for resale, distribution, or commercial use, and does not sell to individual retail consumers.</p>
                        <p><strong class="font-bold text-gray-900">2.2</strong> The Buyer must provide a valid GSTIN or other business registration proof [if applicable] before its first Order.</p>
                        <p><strong class="font-bold text-gray-900">2.3</strong> The minimum order quantity is [quantity/value], unless stated otherwise in the quotation.</p>
                        <p><strong class="font-bold text-gray-900">2.4</strong> The Company may decline or limit any Order, or request further verification, at its discretion.</p>
                        <p><strong class="font-bold text-gray-900">2.5</strong> Wholesale prices and catalogues are for trade Buyers only and must not be quoted to end consumers.</p>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            3
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Orders and Confirmation
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">3.1</strong> An Order becomes binding once the Company issues an Order Confirmation and, where full payment is required before dispatch under Section 4, once that payment is received.</p>
                        <p><strong class="font-bold text-gray-900">3.2</strong> The Buyer must check the product, design, quantity, size, and price on the Order Confirmation and report any error within [24] hours of receiving it.</p>
                        <p><strong class="font-bold text-gray-900">3.3</strong> The Company will not substitute products or reduce quantities without the Buyer's consent, except for Assorted Goods under Section 5.</p>
                        <p><strong class="font-bold text-gray-900">3.4</strong> If an item becomes unavailable after confirmation, the Company will inform the Buyer before dispatch. The Buyer may choose a partial shipment, a back-order, or an agreed substitute; any amount paid for items not supplied will be refunded or credited within [3] working days.</p>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            4
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Prices and Payment
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">4.1</strong> Prices are in Rupees and exclusive of GST, freight, and insurance, unless the quotation states otherwise.</p>
                        <p><strong class="font-bold text-gray-900">4.2</strong> Quotations and proforma invoices are valid for 48 hours. If full payment is not received within that period, the quotation lapses and prices may be revised before a new one is issued.</p>
                        <p><strong class="font-bold text-gray-900">4.3</strong> Full payment is required before dispatch. The Company does not offer credit or cash on delivery (COD).</p>
                        <p><strong class="font-bold text-gray-900">4.4</strong> Payments must be made only to the Company's official bank account; the Company is not responsible for payments made to any other account.</p>
                        <p><strong class="font-bold text-gray-900">4.5</strong> Ownership of the Goods passes to the Buyer once full payment is received and the Goods are dispatched.</p>
                    </div>
                </div>

                <!-- Section 5 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            5
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Product Images, Assorted Designs, and Variations
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">5.1</strong> Images on the Company's website, catalogues, and marketing materials are for reference only; actual design, print placement, colour, pattern, texture, finish, and packaging may vary.</p>
                        <p><strong class="font-bold text-gray-900">5.2</strong> For Assorted Goods, designs, colours, and prints are selected by the Company at the time of packing and may differ from the images shown. A Buyer wanting a specific design must request it in writing before confirmation; this is subject to stock availability and may affect price or lead time.</p>
                        <p><strong class="font-bold text-gray-900">5.3</strong> The following are normal variations and not defects: colour or shade differences between production batches; minor variation in size, weight, or dimensions within [±5]%; colour differences caused by screen display or photography; and the random selection of designs in Assorted Goods.</p>
                        <p><strong class="font-bold text-gray-900">5.4</strong> None of the variations in this Section is grounds for rejection, return, or refund.</p>
                    </div>
                </div>

                <!-- Section 6 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            6
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Dispatch, Delivery, and Transit Risk
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">6.1</strong> Goods are usually dispatched within [3] working days of full payment being received. Dispatch and delivery dates are estimates only; a delay does not entitle the Buyer to cancel the Order or claim compensation.</p>
                        <p><strong class="font-bold text-gray-900">6.2</strong> Goods are sent through [the Company's chosen transporter / a transporter nominated by the Buyer]; freight is [payable by the Buyer / included in the price, as stated in the quotation].</p>
                        <p><strong class="font-bold text-gray-900">6.3</strong> Risk of loss or damage passes to the Buyer once the Goods are handed to the transporter, or collected by the Buyer from the Company's premises.</p>
                        <p><strong class="font-bold text-gray-900">6.4</strong> Loss or damage in transit must be claimed from the transporter or insurer. The Company will share the relevant dispatch documents and reasonably support the claim.</p>
                    </div>
                </div>

                <!-- Section 7 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            7
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Inspection and Claims
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">7.1</strong> At delivery, the Buyer must check the number of cartons or packages against the delivery documents and confirm the outer packaging is intact. Any missing item or visible damage must be noted on the transporter's delivery receipt before signing.</p>
                        <p><strong class="font-bold text-gray-900">7.2</strong> Within [48] hours of delivery, the Buyer must inspect the contents. Any shortage, wrong item, or manufacturing defect must be reported to [support contact] along with the invoice number, clear photographs, and, where possible, an unedited video of the sealed package being opened.</p>
                        <p><strong class="font-bold text-gray-900">7.3</strong> The Company will not accept a claim that is reported after this window, made without the evidence above, concerning Goods that have been used, altered, repacked, or sold on, or concerning any variation described in Section 5.</p>
                        <p><strong class="font-bold text-gray-900">7.4</strong> Where a claim is valid, the Company will, at its choice, replace the affected items, supply the missing items, or issue a credit note for their invoice value. This is the Buyer's sole remedy for shortage, wrong supply, or defects.</p>
                    </div>
                </div>

                <!-- Section 8 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-100 border border-pink-300 text-[#F25996] font-extrabold text-sm shrink-0">
                            8
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                                No Returns, Exchanges, or Refunds
                            </h3>
                            <span class="px-2 py-0.5 rounded-md bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs uppercase tracking-wider">
                                Strict Policy
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">8.1</strong> No Goods may be sent back to the Company without its prior written approval; Goods returned without approval will be refused or held at the Buyer's cost.</p>
                        <p><strong class="font-bold text-gray-900">8.2</strong> Except for valid claims under Section 7 and refunds for unsupplied items under Section 3.4, the Company does not accept returns, exchanges, or refunds once an Order is confirmed.</p>
                        <p><strong class="font-bold text-gray-900">8.3</strong> This applies regardless of the reason given, including change of preference or the variations described in Section 5.</p>
                    </div>
                </div>

                <!-- Section 9 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            9
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Cancellation and Changes
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">9.1</strong> The Buyer cannot cancel an Order once it is binding under Section 3.1.</p>
                        <p><strong class="font-bold text-gray-900">9.2</strong> A request to increase quantity or change specification may be accepted at the Company's discretion before dispatch; any additional amount must be paid in full before dispatch, and no refund is given for a reduction or change.</p>
                        <p><strong class="font-bold text-gray-900">9.3</strong> If the Buyer refuses to accept delivery of paid Goods, no refund will be given, and the Buyer must bear the return freight and any storage charges. Goods not collected within [30] days of written notice may be disposed of or resold by the Company.</p>
                        <p><strong class="font-bold text-gray-900">9.4</strong> The Company may cancel an Order it cannot supply for reasons beyond its control; in that case, any amount paid for the unsupplied Goods will be refunded.</p>
                    </div>
                </div>

                <!-- Section 10 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            10
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Limitation of Liability
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">10.1</strong> The Company's total liability for any Order is limited to the invoice value of the Goods concerned.</p>
                        <p><strong class="font-bold text-gray-900">10.2</strong> The Company is not liable for loss of profit, loss of business, or any indirect or consequential loss.</p>
                        <p><strong class="font-bold text-gray-900">10.3</strong> The Company's liability for the quality of Goods is limited to the remedies in Section 7; to the extent permitted by law, all other conditions and warranties about quality or fitness for a particular purpose are excluded.</p>
                    </div>
                </div>

                <!-- Section 11 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            11
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Force Majeure
                        </h3>
                    </div>
                    <div class="pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p>The Company is not liable for any delay or failure to supply caused by events beyond its reasonable control, including natural disasters, fire, epidemics, strikes, power failure, shortage of raw materials, transport disruption, or government action. If such an event continues for more than [30] days, either party may cancel the unsupplied part of the Order, and any amount paid for it will be refunded.</p>
                    </div>
                </div>

                <!-- Section 12 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            12
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Designs and Intellectual Property
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">12.1</strong> All images, catalogues, designs, and content on the Company's website remain its property and may not be copied or used without written permission.</p>
                        <p><strong class="font-bold text-gray-900">12.2</strong> Where the Buyer supplies its own design, logo, or artwork for use on the Goods or packaging, the Buyer confirms it owns or has the right to use it, and will indemnify the Company against any claim that it infringes another person's rights.</p>
                    </div>
                </div>

                <!-- Section 13 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            13
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Privacy
                        </h3>
                    </div>
                    <div class="pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p>The Company collects and uses the Buyer's business and contact details only to process Orders, deliver Goods, and communicate about its products. Further details are set out in the Company's Privacy Policy.</p>
                    </div>
                </div>

                <!-- Section 14 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            14
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            General
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">14.1</strong> By placing an Order through any channel, the Buyer agrees to these Terms, which apply to every Order even if not separately referenced on the purchase order, invoice, or delivery note.</p>
                        <p><strong class="font-bold text-gray-900">14.2</strong> These Terms override any terms in the Buyer's purchase order, unless an authorised representative of the Company agrees otherwise in writing.</p>
                        <p><strong class="font-bold text-gray-900">14.3</strong> The Company may update these Terms at any time; the version in effect at the time an Order is confirmed applies to that Order.</p>
                        <p><strong class="font-bold text-gray-900">14.4</strong> If any clause is found invalid or unenforceable, the remaining clauses continue to apply.</p>
                        <p><strong class="font-bold text-gray-900">14.5</strong> If the Company does not enforce a clause at any time, it does not lose the right to enforce it later.</p>
                    </div>
                </div>

                <!-- Section 15 -->
                <div class="pt-6">
                    <div class="flex items-start gap-3 sm:gap-4 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                            15
                        </span>
                        <h3 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                            Governing Law and Disputes
                        </h3>
                    </div>
                    <div class="space-y-2.5 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                        <p><strong class="font-bold text-gray-900">15.1</strong> These Terms are governed by the laws of [India\ Tamil Nadu].</p>
                        <p><strong class="font-bold text-gray-900">15.2</strong> The parties will first try to resolve any dispute through good-faith discussion within [15] days.</p>
                        <p><strong class="font-bold text-gray-900">15.3</strong> Any unresolved dispute is subject to the exclusive jurisdiction of the courts at [coimbatore], [India\ Tamil Nadu].</p>
                    </div>
                </div>

            </div>

            <!-- Bottom Support Callout Box -->
            <div class="mt-8 rounded-2xl bg-gradient-to-r from-pink-50 via-pink-50 to-pink-100/70 border border-pink-200/80 p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3.5 w-full sm:w-auto">
                    <div class="w-10 h-10 rounded-full bg-[#F25996] text-white flex items-center justify-center flex-shrink-0 font-serif font-bold text-lg shadow-sm">
                        i
                    </div>
                    <p class="text-slate-700 font-medium text-sm sm:text-base leading-snug">
                        If you have any questions about our terms, please contact our support team before placing an order.
                    </p>
                </div>
                
                <a href="mailto:grmb2bsupport@gmail.com" 
                   class="inline-flex items-center justify-center gap-2 bg-[#F25996] hover:bg-[#d8407d] active:bg-[#c2346e] text-white font-bold px-6 py-3 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 text-sm whitespace-nowrap w-full sm:w-auto text-center transform hover:-translate-y-0.5">
                    <span>Contact Us</span>
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

        </div>
    </div>

</div>


