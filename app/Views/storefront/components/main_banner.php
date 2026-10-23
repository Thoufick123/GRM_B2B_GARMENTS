<?php
if (!function_exists('renderBannerSection')) {
function renderBannerSection($data, $prefix, $uniqueId, $dynamicData) {
    if (empty($data)) return;

    $contentType = $data['content_type'] ?? 'custom_image';
    $imageFit = $data['image_fit'] ?? 'cover';
    $objectFitClass = 'object-cover';
    if ($imageFit === 'contain') $objectFitClass = 'object-contain';
    elseif ($imageFit === 'fill') $objectFitClass = 'object-fill';
    $fitStyle = 'object-fit: ' . htmlspecialchars($imageFit) . ';';
    
    $media = $data['media'] ?? [];
    $title = $data['title'] ?? '';
    $subtitle = $data['subtitle'] ?? '';
    $desc = $data['description'] ?? '';
    $btnText = trim((string)($data['button_text'] ?? ''));
    $ctaUrl = trim((string)($data['cta_url'] ?? ''));
    if ($ctaUrl === '') {
        if (in_array($contentType, ['custom_product_carousel', 'selected_custom_products', 'custom_category_grid', 'selected_custom_categories'], true)) {
            $ctaUrl = '/customize';
        } else {
            $ctaUrl = '/catalog';
        }
    }
    $theme = $data['theme'] ?? [];
    $btnBg = $theme['button_bg_color'] ?? '#111827';
    $btnTextCol = $theme['button_text_color'] ?? '#ffffff';
    $normalizeCssColor = static function ($value, string $fallback): string {
        $value = trim((string)$value);
        if ($value === '') return $fallback;
        if (preg_match('/^#[0-9a-fA-F]{7}$/', $value)) {
            return substr($value, 0, 7);
        }
        return $value;
    };
    $btnBg = $normalizeCssColor($btnBg, '#111827');
    $btnTextCol = $normalizeCssColor($btnTextCol, '#ffffff');
    $renderCta = static function () use ($btnText, $ctaUrl, $btnBg, $btnTextCol, $uniqueId): void {
        if ($btnText === '') return;
        if ($ctaUrl === '') {
            $ctaUrl = '/catalog';
        }
        ?>
        <a href="<?= htmlspecialchars($ctaUrl) ?>" class="mb-cta-btn-<?= $uniqueId ?> absolute bottom-0 right-0 pointer-events-auto flex items-center justify-center hover:opacity-90 transition-opacity group/btn shadow-xl" style="z-index: 30; background-color:<?= $btnBg ?>; color:<?= $btnTextCol ?>;">
            <span class="mb-cta-text-<?= $uniqueId ?> font-bold tracking-wide"><?= htmlspecialchars($btnText) ?></span>
        </a>
        <?php
    };

    if ($contentType === 'custom_image') {
        // HERO-STYLE CUSTOM SLIDER
        $mediaItems = array_values(array_filter($media, function($m){
            return !empty(trim($m['desktop'] ?? '')) || !empty(trim($m['mobile'] ?? ''));
        }));
        $showArrows = ($data['show_arrows'] ?? true) !== false;
        $showDots   = ($data['show_dots']   ?? true) !== false;
        $aCol = $theme['arrow_color'] ?? '#ffffff';
        $aBg  = $theme['arrow_bg_color'] ?? '#000000';
        $dotColor = $theme['dot_color'] ?? '#8c6a4f';
        $sliderId = 'mbslider_' . $prefix . '_' . $uniqueId;
        $align = $data['alignment'] ?? 'center';
        if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
        elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
        elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
        else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>

        <!-- Slide Track -->
        <div class="slider-track-mb w-full h-full absolute inset-0 rounded-inherit overflow-hidden" id="<?= $sliderId ?>">
            <?php foreach($mediaItems as $idx => $m): 
                $dRaw = trim((string)($m['desktop'] ?? ''));
                $mRaw = trim((string)($m['mobile'] ?? ''));
                if ($dRaw === '' && $mRaw !== '') $dRaw = $mRaw;
                if ($mRaw === '' && $dRaw !== '') $mRaw = $dRaw;
                if ($dRaw === '') continue;

                $dUrl = get_image_url($dRaw, 1600, 800);
                $mUrl = get_image_url($mRaw, 800, 800);

                $overlayOpacity = isset($m['opacity']) && $m['opacity'] !== '' ? (float)$m['opacity'] : 0;
                $overlayFrac = $overlayOpacity > 1 ? ($overlayOpacity / 100) : $overlayOpacity;
                $overlayFrac = max(0, min(1, $overlayFrac));

                $defaultType = $m['type'] ?? 'image';
                $checkIsVideo = function($url) use ($defaultType) {
                    if (empty($url)) return false;
                    if (preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $url) || str_contains($url, '/video/upload/')) return true;
                    if (preg_match('/\.(jpg|jpeg|png|webp|gif|svg|avif)(\?.*)?$/i', $url) || str_contains($url, '/image/upload/')) return false;
                    return $defaultType === 'video';
                };

                $dIsVideo = $checkIsVideo($dRaw);
                $mIsVideo = $checkIsVideo($mRaw);
                $isSameMedia = ($dRaw === $mRaw && $dIsVideo === $mIsVideo);
            ?>
                <div class="slide-mb absolute inset-0 w-full h-full transition-all duration-700 ease-in-out <?= $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>" data-index="<?= $idx ?>">
                    <?php if ($isSameMedia): ?>
                        <?php if ($dIsVideo): ?>
                            <video src="<?= htmlspecialchars($dUrl) ?>" autoplay loop muted playsinline class="absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($dUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" decoding="async" width="1600" height="800">
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Desktop Media (>= 768px) -->
                        <?php if ($dIsVideo): ?>
                            <video src="<?= htmlspecialchars($dUrl) ?>" autoplay loop muted playsinline class="hidden md:block absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($dUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="hidden md:block absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" decoding="async" width="1600" height="800">
                        <?php endif; ?>

                        <!-- Mobile Media (< 768px) -->
                        <?php if ($mIsVideo): ?>
                            <video src="<?= htmlspecialchars($mUrl) ?>" autoplay loop muted playsinline class="block md:hidden absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($mUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="block md:hidden absolute inset-0 w-full h-full <?= $objectFitClass ?>" style="<?= $fitStyle ?>" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>" decoding="async" width="800" height="800">
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($overlayFrac > 0): ?>
                        <div class="absolute inset-0 pointer-events-none" style="background-color: rgba(0, 0, 0, <?= $overlayFrac ?>);"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation Arrows (Controlled by CMS Settings) -->
        <?php if($showArrows && !empty($mediaItems)): ?>
        <button class="mb-slider-prev-<?= $sliderId ?> absolute left-2 md:left-3 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="color: <?= htmlspecialchars($aCol) ?>; background-color: <?= htmlspecialchars($aBg) ?>;" aria-label="Previous Slide">
            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button class="mb-slider-next-<?= $sliderId ?> absolute right-2 md:right-3 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="color: <?= htmlspecialchars($aCol) ?>; background-color: <?= htmlspecialchars($aBg) ?>;" aria-label="Next Slide">
            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <?php endif; ?>

        <!-- Dots -->
        <?php if($showDots && count($mediaItems) > 0): ?>
        <div class="mb-dots-container-<?= $uniqueId ?> absolute left-1/2 flex items-center gap-2 sm:gap-3" style="transform: translateX(-50%); z-index: 50;">
            <?php foreach($mediaItems as $idx => $m): ?>
                <button class="mb-dot-<?= $sliderId ?> slider-dot-mb relative z-50 <?= $idx === 0 ? 'active' : '' ?>" style="background-color: <?= htmlspecialchars($dotColor) ?> !important; <?= $idx === 0 ? 'outline: 2px solid ' . htmlspecialchars($dotColor) . ' !important; outline-offset: 2px; opacity: 1;' : 'outline: none !important; opacity: 0.5;' ?>" data-index="<?= $idx ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <?php if($btnText): ?>
        <a href="<?= htmlspecialchars($ctaUrl) ?>" class="mb-cta-btn-<?= $uniqueId ?> absolute bottom-0 right-0 pointer-events-auto flex items-center justify-center hover:opacity-90 transition-opacity group/btn shadow-xl" style="z-index: 30; background-color:<?= $btnBg ?>; color:<?= $btnTextCol ?>;">
            <span class="mb-cta-text-<?= $uniqueId ?> font-bold tracking-wide"><?= htmlspecialchars($btnText) ?></span>
        </a>
        <?php endif; ?>

        <!-- Hero-style Slider JS -->
        <script>
        (function(){
            var sid = <?= json_encode($sliderId) ?>;
            var anim = <?= json_encode($data['animation'] ?? 'fade') ?>;
            var dotColor = <?= json_encode($dotColor) ?>;

            document.addEventListener('DOMContentLoaded', function(){
                var container = document.getElementById(sid);
                if (!container) return;
                var slides = container.querySelectorAll('.slide-mb');
                var dots   = document.querySelectorAll('.mb-dot-' + sid);
                var prevBtn = document.querySelector('.mb-slider-prev-' + sid);
                var nextBtn = document.querySelector('.mb-slider-next-' + sid);
                var total = slides.length;
                if (total === 0) return;
                var cur = 0;
                var timer;

                function goTo(n, isNext) {
                    if (n === cur || total < 2) return;
                    var from = slides[cur], to = slides[n];
                    slides.forEach(function(s){ s.style.transition = 'all 0.7s ease-in-out'; s.style.zIndex = '0'; });
                    from.style.zIndex = '1'; to.style.zIndex = '2'; to.style.opacity = '1';
                    if (anim === 'fade') {
                        from.style.opacity = '0';
                    } else if (anim === 'slide_left' || anim === 'slide_right') {
                        var dir = (anim === 'slide_left') ? (isNext ? 1 : -1) : (isNext ? -1 : 1);
                        from.style.transform = 'translateX(' + (-dir * 100) + '%)';
                        to.style.transform = 'translateX(' + (dir * 100) + '%)';
                        void to.offsetWidth;
                        to.style.transform = 'translateX(0)';
                        setTimeout(function(){ from.style.opacity='0'; from.style.transform='translateX(0)'; }, 700);
                    } else if (anim === 'zoom') {
                        from.style.opacity = '0'; from.style.transform = 'scale(1.1)';
                        to.style.transform = 'scale(0.9)';
                        void to.offsetWidth;
                        to.style.transform = 'scale(1)';
                        setTimeout(function(){ from.style.transform='scale(1)'; }, 700);
                    } else {
                        from.style.opacity = '0';
                    }
                    dots.forEach(function(d, i){
                        if (i === n) {
                            d.classList.add('active');
                            d.style.opacity = '1';
                            d.style.backgroundColor = dotColor;
                            d.style.outline = '2px solid ' + dotColor;
                            d.style.outlineOffset = '2px';
                            d.onmouseover = null; d.onmouseout = null;
                        } else {
                            d.classList.remove('active');
                            d.style.opacity = '0.5';
                            d.style.backgroundColor = dotColor;
                            d.style.outline = 'none';
                            d.onmouseover = function(){ this.style.opacity = '0.8'; };
                            d.onmouseout  = function(){ this.style.opacity = '0.5'; };
                        }
                    });
                    cur = n;
                    resetTimer();
                }
                function next(){ goTo((cur+1)%total, true); }
                function prev(){ goTo((cur-1+total)%total, false); }
                function resetTimer(){ clearInterval(timer); timer = setInterval(next, 5000); }

                if (nextBtn) nextBtn.addEventListener('click', next);
                if (prevBtn) prevBtn.addEventListener('click', prev);
                dots.forEach(function(d,i){ d.addEventListener('click', function(){ goTo(i, i > cur); }); });
                if (total > 1) resetTimer();
            });
        })();
        </script>

        <?php
    } 
    elseif ($contentType === 'product_carousel') {
        // DYNAMIC PRODUCTS
        $items = $dynamicData['product_carousel'];
        ?>
        <?php
            $pcShowArrows = ($data['show_arrows'] ?? true) !== false;
            $pcShowDots   = ($data['show_dots']   ?? true) !== false;
            $aColPC = $theme['arrow_color'] ?? '#000000';
            $aBgPC  = $theme['arrow_bg_color'] ?? '#ffffff';
        ?>
        <?php
            $align = $data['alignment'] ?? 'center';
            if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
            elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
            elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
            else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> p-8 z-10 pointer-events-none">
            <div class="max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-xs sm:text-sm md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-2 md:mb-4 uppercase drop-shadow-sm"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title font-extrabold mb-3 md:mb-6 drop-shadow-md leading-tight" style="font-size: clamp(1.5rem, 5vw, 4.5rem);"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-4 md:mb-6" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach(array_slice($items, 0, 8) as $prod): 
                    $imgPath = $prod['main_image'] ?? '';
                    $img = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($prod['name']);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/product?id=<?= $prod['id'] ?>" class="block w-full h-full absolute inset-0">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($pcShowArrows && !empty($items)): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgPC ?>; color:<?= $aColPC ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgPC ?>; color:<?= $aColPC ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($pcShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'category_grid') {
        // DYNAMIC CATEGORIES
        $items = $dynamicData['category_grid'];
        ?>
        <?php
            $cgShowArrows = ($data['show_arrows'] ?? true) !== false;
            $cgShowDots   = ($data['show_dots']   ?? true) !== false;
            $aColCG = $theme['arrow_color'] ?? '#000000';
            $aBgCG  = $theme['arrow_bg_color'] ?? '#ffffff';
        ?>
        <?php
            $align = $data['alignment'] ?? 'center';
            if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
            elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
            elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
            else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach(array_slice($items, 0, 8) as $cat): 
                    $imgPath = $cat['image_path'] ?? '';
                    $img = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($cat['name']);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/catalog?category=<?= $cat['id'] ?>" class="block w-full h-full absolute inset-0">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($cgShowArrows && count($items) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCG ?>; color:<?= $aColCG ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCG ?>; color:<?= $aColCG ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($cgShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'selected_products') {
        $selProdIds = $data['selected_products'] ?? [];
        if (empty($selProdIds)) return;
        ?>
        <?php
            $spShowArrows = ($data['show_arrows'] ?? true) !== false;
            $align = $data['alignment'] ?? 'center';
            if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
            elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
            elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
            else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach($selProdIds as $prodId): 
                    $prod = $dynamicData['selected_products'][$prodId] ?? null;
                    if (!$prod) continue;
                    
                    $imgPath = $prod['main_image'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($prod['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/product?id=<?= $prod['id'] ?>" class="block w-full h-full absolute inset-0">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php 
                $spShowDots   = ($data['show_dots']   ?? true) !== false;
                $aColSP = $theme['arrow_color'] ?? '#000000';
                $aBgSP  = $theme['arrow_bg_color'] ?? '#ffffff';
            ?>
            <?php if($spShowArrows && count($selProdIds) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSP ?>; color:<?= $aColSP ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSP ?>; color:<?= $aColSP ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($spShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'selected_categories') {
        $selCatIds = $data['selected_categories'] ?? [];
        if (empty($selCatIds)) return;
        ?>
        <?php
            $scShowArrows = ($data['show_arrows'] ?? true) !== false;
            $align = $data['alignment'] ?? 'center';
            if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
            elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
            elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
            else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach($selCatIds as $catId): 
                    $cat = $dynamicData['selected_categories'][$catId] ?? null;
                    if (!$cat) continue;
                    
                    $imgPath = $cat['image_path'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($cat['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/catalog?category=<?= $cat['id'] ?>" class="block w-full h-full absolute inset-0">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php 
                $scShowDots   = ($data['show_dots']   ?? true) !== false;
                $aColSC = $theme['arrow_color'] ?? '#000000';
                $aBgSC  = $theme['arrow_bg_color'] ?? '#ffffff';
            ?>
            <?php if($scShowArrows && count($selCatIds) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSC ?>; color:<?= $aColSC ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSC ?>; color:<?= $aColSC ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($scShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'custom_product_carousel') {
        $items = $dynamicData['custom_product_carousel'] ?? [];
        $cpcShowArrows = ($data['show_arrows'] ?? true) !== false;
        $cpcShowDots   = ($data['show_dots']   ?? true) !== false;
        $aColCPC = $theme['arrow_color'] ?? '#000000';
        $aBgCPC  = $theme['arrow_bg_color'] ?? '#ffffff';
        $align = $data['alignment'] ?? 'center';
        if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
        elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
        elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
        else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach(array_slice($items, 0, 8) as $prod): 
                    $imgPath = $prod['main_image'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($prod['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/fabrics/customize?fabric_id=<?= (int)$prod['id'] ?>" class="block w-full h-full absolute inset-0" title="<?= htmlspecialchars($prod['name']) ?>">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($cpcShowArrows && !empty($items)): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCPC ?>; color:<?= $aColCPC ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCPC ?>; color:<?= $aColCPC ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($cpcShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'selected_custom_products') {
        $selCustomProdIds = $data['selected_custom_products'] ?? [];
        if (empty($selCustomProdIds)) return;
        $scpShowArrows = ($data['show_arrows'] ?? true) !== false;
        $align = $data['alignment'] ?? 'center';
        if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
        elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
        elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
        else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach($selCustomProdIds as $prodId): 
                    $prod = $dynamicData['selected_custom_products'][$prodId] ?? null;
                    if (!$prod) continue;
                    
                    $imgPath = $prod['main_image'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($prod['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/fabrics/customize?fabric_id=<?= (int)$prod['id'] ?>" class="block w-full h-full absolute inset-0" title="<?= htmlspecialchars($prod['name']) ?>">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php 
                $scpShowDots = ($data['show_dots'] ?? true) !== false;
                $aColSCP = $theme['arrow_color'] ?? '#000000';
                $aBgSCP  = $theme['arrow_bg_color'] ?? '#ffffff';
            ?>
            <?php if($scpShowArrows && count($selCustomProdIds) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSCP ?>; color:<?= $aColSCP ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSCP ?>; color:<?= $aColSCP ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($scpShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'custom_category_grid') {
        $items = $dynamicData['custom_category_grid'] ?? [];
        $ccgShowArrows = ($data['show_arrows'] ?? true) !== false;
        $ccgShowDots   = ($data['show_dots']   ?? true) !== false;
        $aColCCG = $theme['arrow_color'] ?? '#000000';
        $aBgCCG  = $theme['arrow_bg_color'] ?? '#ffffff';
        $align = $data['alignment'] ?? 'center';
        if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
        elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
        elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
        else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach(array_slice($items, 0, 8) as $cat): 
                    $imgPath = $cat['image_path'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($cat['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/customize?category_id=<?= (int)$cat['id'] ?>" class="block w-full h-full absolute inset-0" title="<?= htmlspecialchars($cat['name']) ?>">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($ccgShowArrows && count($items) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCCG ?>; color:<?= $aColCCG ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgCCG ?>; color:<?= $aColCCG ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($ccgShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
    elseif ($contentType === 'selected_custom_categories') {
        $selCustomCatIds = $data['selected_custom_categories'] ?? [];
        if (empty($selCustomCatIds)) return;
        $sccShowArrows = ($data['show_arrows'] ?? true) !== false;
        $align = $data['alignment'] ?? 'center';
        if ($align === 'left') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-center'; }
        elseif ($align === 'right') { $alignClass = 'items-end text-right'; $justifyClass = 'justify-center'; }
        elseif ($align === 'bottom') { $alignClass = 'items-start text-left'; $justifyClass = 'justify-end pb-16'; }
        else { $alignClass = 'items-center text-center'; $justifyClass = 'justify-center'; }
        ?>
        <!-- Text Overlay -->
        <div class="absolute inset-0 flex flex-col <?= $justifyClass ?> <?= $alignClass ?> px-4 pt-2 pb-12 sm:pb-14 md:p-8 z-10 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-xl">
                <?php if($subtitle): ?>
                    <p class="mb-subtitle text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
                <?php if($title): ?>
                    <h3 class="mb-title text-lg sm:text-2xl md:text-5xl font-extrabold mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight"><?= htmlspecialchars($title) ?></h3>
                <?php endif; ?>
                <?php if($desc): ?>
                    <p class="mb-desc text-[11px] sm:text-sm md:text-base lg:text-lg max-w-xl md:max-w-2xl font-medium drop-shadow-sm mb-2 sm:mb-4 md:mb-6 leading-tight sm:leading-normal" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($desc)) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="swiper mySwiper-<?= $prefix ?>-<?= $uniqueId ?> w-full h-full absolute inset-0 z-0 rounded-inherit overflow-hidden">
            <div class="swiper-wrapper h-full">
                <?php foreach($selCustomCatIds as $catId): 
                    $cat = $dynamicData['selected_custom_categories'][$catId] ?? null;
                    if (!$cat) continue;
                    
                    $imgPath = $cat['image_path'] ?? '';
                    $rawImg = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : (BASE_URL . '/' . ltrim($imgPath, '/'))) : 'https://placehold.co/400?text=' . urlencode($cat['name']);
                    $img = get_image_url($rawImg, 400, 400);
                ?>
                    <div class="swiper-slide w-full h-full relative">
                        <a href="<?= BASE_URL ?>/customize?category_id=<?= (int)$cat['id'] ?>" class="block w-full h-full absolute inset-0" title="<?= htmlspecialchars($cat['name']) ?>">
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full <?= $objectFitClass ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" decoding="async" width="400" height="400">
                            <div class="absolute inset-0 bg-black/10 transition-opacity hover:bg-black/20"></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php 
                $sccShowDots   = ($data['show_dots']   ?? true) !== false;
                $aColSCC = $theme['arrow_color'] ?? '#000000';
                $aBgSCC  = $theme['arrow_bg_color'] ?? '#ffffff';
            ?>
            <?php if($sccShowArrows && count($selCustomCatIds) > 1): ?>
                <button class="swiper-button-prev-custom absolute left-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSCC ?>; color:<?= $aColSCC ?>;" aria-label="Previous">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button class="swiper-button-next-custom absolute right-2 top-1/2 -translate-y-1/2 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all focus:outline-none" style="background-color:<?= $aBgSCC ?>; color:<?= $aColSCC ?>;" aria-label="Next">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            <?php endif; ?>

            <?php if($sccShowDots): ?>
                <div class="swiper-pagination-custom" style="position:absolute; bottom:0.625rem; left:0; right:0; z-index:20; display:flex; justify-content:center; align-items:center; gap:0.25rem;"></div>
            <?php endif; ?>
        </div>
        <?php $renderCta(); ?>
        <?php
    }
}
}

$title = $contentData['title'] ?? '';
$uniqueId = 'mb_' . uniqid();

$sectionsData = [
    'left' => $contentData['left'] ?? [],
    'right_top' => $contentData['right_top'] ?? [],
    'right_bottom' => $contentData['right_bottom'] ?? []
];

// Fetch dynamic data if needed
$db = \Core\Database::getInstance();
$dynamicData = [
    'product_carousel' => [], 
    'category_grid' => [],
    'selected_products' => [],
    'selected_categories' => [],
    'custom_product_carousel' => [],
    'custom_category_grid' => [],
    'selected_custom_products' => [],
    'selected_custom_categories' => []
];

$needProducts = false;
$needCategories = false;
$needCustomProducts = false;
$needCustomCategories = false;
$selProdIds = [];
$selCatIds = [];
$selCustomProdIds = [];
$selCustomCatIds = [];

foreach ($sectionsData as $data) {
    if (isset($data['content_type'])) {
        if ($data['content_type'] === 'product_carousel') $needProducts = true;
        if ($data['content_type'] === 'category_grid') $needCategories = true;
        if ($data['content_type'] === 'custom_product_carousel') $needCustomProducts = true;
        if ($data['content_type'] === 'custom_category_grid') $needCustomCategories = true;
        if ($data['content_type'] === 'selected_products' && !empty($data['selected_products'])) {
            $selProdIds = array_merge($selProdIds, $data['selected_products']);
        }
        if ($data['content_type'] === 'selected_categories' && !empty($data['selected_categories'])) {
            $selCatIds = array_merge($selCatIds, $data['selected_categories']);
        }
        if ($data['content_type'] === 'selected_custom_products' && !empty($data['selected_custom_products'])) {
            $selCustomProdIds = array_merge($selCustomProdIds, $data['selected_custom_products']);
        }
        if ($data['content_type'] === 'selected_custom_categories' && !empty($data['selected_custom_categories'])) {
            $selCustomCatIds = array_merge($selCustomCatIds, $data['selected_custom_categories']);
        }
    }
}

$mbCacheKey = 'mb_dyn_' . md5(json_encode([$needProducts, $needCategories, $needCustomProducts, $needCustomCategories, $selProdIds, $selCatIds, $selCustomProdIds, $selCustomCatIds]));
$dynamicData = (empty($_GET['preview']) && class_exists('\Core\Cache')) ? \Core\Cache::get($mbCacheKey) : null;

if ($dynamicData === null) {
    $dynamicData = [
        'product_carousel' => [], 
        'category_grid' => [],
        'selected_products' => [],
        'selected_categories' => [],
        'custom_product_carousel' => [],
        'custom_category_grid' => [],
        'selected_custom_products' => [],
        'selected_custom_categories' => []
    ];

    if ($needProducts) {
        $stmt = $db->query("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image FROM products p JOIN categories c ON p.category_id = c.id AND c.status = 'active' WHERE p.status = 'active' AND p.category_id IS NOT NULL AND (p.is_customizable = 0 OR p.is_customizable IS NULL) ORDER BY p.id DESC LIMIT 4");
        $dynamicData['product_carousel'] = $stmt->fetchAll();
    }
    if ($needCategories) {
        $stmt = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY id ASC LIMIT 4");
        $dynamicData['category_grid'] = $stmt->fetchAll();
    }
    if ($needCustomProducts) {
        $stmt = $db->query("
            SELECT p.*, fc.id as customization_id, fc.program_name, fc.garment_type,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) as main_image 
            FROM products p 
            JOIN fabric_customizations fc ON fc.fabric_id = p.id 
            WHERE p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1 
            ORDER BY p.id DESC LIMIT 8
        ");
        $dynamicData['custom_product_carousel'] = $stmt->fetchAll();
    }
    if ($needCustomCategories) {
        $stmt = $db->query("
            SELECT DISTINCT c.* 
            FROM categories c 
            JOIN products p ON (p.category_id = c.id) 
            JOIN fabric_customizations fc ON (fc.fabric_id = p.id) 
            WHERE c.status = 'active' AND p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1 
            ORDER BY c.name ASC LIMIT 8
        ");
        $dynamicData['custom_category_grid'] = $stmt->fetchAll();
    }
    if (!empty($selProdIds)) {
        $ids = implode(',', array_map('intval', array_unique($selProdIds)));
        $stmt = $db->query("SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as main_image FROM products p JOIN categories c ON p.category_id = c.id AND c.status = 'active' WHERE p.id IN ($ids) AND p.status = 'active' AND p.category_id IS NOT NULL");
        foreach ($stmt->fetchAll() as $p) {
            $dynamicData['selected_products'][$p['id']] = $p;
        }
    }
    if (!empty($selCatIds)) {
        $ids = implode(',', array_map('intval', array_unique($selCatIds)));
        $stmt = $db->query("SELECT * FROM categories WHERE id IN ($ids) AND status = 'active'");
        foreach ($stmt->fetchAll() as $c) {
            $dynamicData['selected_categories'][$c['id']] = $c;
        }
    }
    if (!empty($selCustomProdIds)) {
        $ids = implode(',', array_map('intval', array_unique($selCustomProdIds)));
        $stmt = $db->query("
            SELECT p.*, fc.id as customization_id, fc.program_name, fc.garment_type,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) as main_image 
            FROM products p 
            JOIN fabric_customizations fc ON fc.fabric_id = p.id 
            WHERE p.id IN ($ids) AND p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1
        ");
        foreach ($stmt->fetchAll() as $p) {
            $dynamicData['selected_custom_products'][$p['id']] = $p;
        }
    }
    if (!empty($selCustomCatIds)) {
        $ids = implode(',', array_map('intval', array_unique($selCustomCatIds)));
        $stmt = $db->query("SELECT * FROM categories WHERE id IN ($ids) AND status = 'active'");
        foreach ($stmt->fetchAll() as $c) {
            $dynamicData['selected_custom_categories'][$c['id']] = $c;
        }
    }

    if (empty($_GET['preview']) && class_exists('\Core\Cache')) {
        \Core\Cache::set($mbCacheKey, $dynamicData, 3600);
    }
}

$containerHeight = !empty($contentData['container_height']) ? $contentData['container_height'] : '';
if (is_numeric($containerHeight)) $containerHeight .= 'vh';

$mobileContainerHeight = !empty($contentData['mobile_container_height']) ? $contentData['mobile_container_height'] : '';
if (is_numeric($mobileContainerHeight)) $mobileContainerHeight .= 'vh';
?>

<!-- Swiper assets are loaded deferred in <head> -->

<style>
<?php foreach ($sectionsData as $prefix => $data):
    if (empty($data)) continue;
    $theme = $data['theme'] ?? [];
    $type = $theme['type'] ?? 'solid';
    $val = $theme['value'] ?? 'transparent';
    
    $bgStyle = "background: {$val};";
    if ($type === 'css') {
        if (strpos($val, 'background:') !== false || strpos($val, 'background-color:') !== false) {
            $bgStyle = trim($val);
            if (substr($bgStyle, -1) !== ';') $bgStyle .= ';';
        }
    }
?>
    .mb-<?= $prefix ?>-<?= $uniqueId ?> { <?= $bgStyle ?> }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .mb-title { color: <?= $theme['title_color'] ?? '#ffffff' ?>; }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .mb-subtitle { color: <?= $theme['subtitle_color'] ?? '#ffffff' ?>; }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .mb-desc { color: <?= $theme['description_color'] ?? '#ffffff' ?>; }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .slider-dot-mb {
        background-color: <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
    }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .slider-dot-mb.active {
        background-color: <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
        outline: 2px solid <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
        outline-offset: 2px;
    }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet {
        background-color: <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
    }
    .mb-<?= $prefix ?>-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet-active {
        background-color: <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
        outline: 2px solid <?= htmlspecialchars($theme['dot_color'] ?? '#8c6a4f') ?> !important;
        outline-offset: 2px !important;
    }

<?php endforeach; ?>

    /* Global Mobile Container Height */
    <?php if ($mobileContainerHeight): ?>
        .mb-left-<?= $uniqueId ?>, .mb-right-stack-<?= $uniqueId ?> { height: <?= htmlspecialchars($mobileContainerHeight) ?>; min-height: 0 !important; }
        .mb-right_top-<?= $uniqueId ?>, .mb-right_bottom-<?= $uniqueId ?> { min-height: 0 !important; }
    <?php else: ?>
        .mb-left-<?= $uniqueId ?>, .mb-right-stack-<?= $uniqueId ?> { height: 100%; min-height: 0 !important; }
        .mb-right_top-<?= $uniqueId ?>, .mb-right_bottom-<?= $uniqueId ?> { min-height: 0 !important; }
    <?php endif; ?>

    /* CTA Button Base (Mobile) */
    .mb-cta-btn-<?= $uniqueId ?> { border-top-left-radius: 1.15rem; padding: 0.4rem 0.95rem; }
    .mb-cta-text-<?= $uniqueId ?> { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .mb-dots-container-<?= $uniqueId ?> { bottom: 0.625rem; }
    .slider-dot-mb {
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        padding: 0;
        transition: all 0.3s ease;
        opacity: 0.5;
        display: inline-block;
        vertical-align: middle;
    }
    .slider-dot-mb.active {
        width: 8px;
        height: 8px;
        opacity: 1;
        outline-offset: 2px;
    }
    .mb-right_top-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?>,
    .mb-right_bottom-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?> { padding: 0.25rem 0.65rem; border-top-left-radius: 0.85rem; }
    .mb-right_top-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?>,
    .mb-right_bottom-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?> { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }

    /* Swiper bullets styling (Mobile) */
    .swiper-pagination-custom {
        bottom: 0.625rem !important;
    }
    .swiper-pagination-custom .swiper-pagination-bullet {
        width: 6px !important;
        height: 6px !important;
        opacity: 0.5 !important;
        border-radius: 9999px !important;
        border: none !important;
        outline: none !important;
        margin: 0 2px !important;
        transition: all 0.3s ease !important;
        display: inline-block !important;
        vertical-align: middle !important;
        cursor: pointer !important;
        padding: 0 !important;
    }
    .swiper-pagination-custom .swiper-pagination-bullet-active {
        width: 8px !important;
        height: 8px !important;
        opacity: 1 !important;
        outline-offset: 2px !important;
    }

    @media (min-width: 1024px) {
        <?php if ($containerHeight): ?>
        /* Global Desktop Container Height */
        .mb-left-<?= $uniqueId ?>, .mb-right-stack-<?= $uniqueId ?> { height: <?= htmlspecialchars($containerHeight) ?>; min-height: 0 !important; }
        .mb-right_top-<?= $uniqueId ?>, .mb-right_bottom-<?= $uniqueId ?> { min-height: 0 !important; }
        <?php else: ?>
        .mb-left-<?= $uniqueId ?>, .mb-right-stack-<?= $uniqueId ?> { height: auto; }
        <?php endif; ?>

        /* Desktop CTA Button Size Enhancement */
        .mb-cta-btn-<?= $uniqueId ?> { border-top-left-radius: 1.85rem; padding: 0.85rem 2.15rem; }
        .mb-cta-text-<?= $uniqueId ?> { font-size: 1.05rem; letter-spacing: 0.06em; }
        .mb-left-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?> { border-top-left-radius: 1.85rem; padding: 0.85rem 2.15rem; }
        .mb-left-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?> { font-size: 1.05rem; letter-spacing: 0.06em; }

        .mb-right_top-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?>,
        .mb-right_bottom-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?> { padding: 0.45rem 1.15rem; border-top-left-radius: 1.15rem; }
        .mb-right_top-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?>,
        .mb-right_bottom-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?> { font-size: 0.78rem; letter-spacing: 0.05em; }

        /* Desktop Arrow Button Size Enhancement */
        [class*="mb-slider-prev-"],
        [class*="mb-slider-next-"] {
            width: 2.75rem !important;
            height: 2.75rem !important;
        }
        [class*="mb-slider-prev-"] {
            left: 1.25rem !important;
        }
        [class*="mb-slider-next-"] {
            right: 1.25rem !important;
        }
        [class*="mb-slider-prev-"] svg,
        [class*="mb-slider-next-"] svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
        }

        .swiper-button-prev-custom,
        .swiper-button-next-custom {
            width: 2.25rem !important;
            height: 2.25rem !important;
        }
        .swiper-button-prev-custom svg,
        .swiper-button-next-custom svg {
            width: 1.15rem !important;
            height: 1.15rem !important;
        }
    }

    @media (min-width: 768px) {
        /* Left Container CTA Button Desktop (Prominent & Handsome) */
        .mb-left-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?> { border-top-left-radius: 1.5rem; padding: 0.6rem 1.45rem; }
        .mb-left-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?> { font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .mb-left-<?= $uniqueId ?> .mb-dots-container-<?= $uniqueId ?> { bottom: 1.25rem; }
        .mb-left-<?= $uniqueId ?> .slider-dot-mb {
            width: 10px;
            height: 10px;
        }
        .mb-left-<?= $uniqueId ?> .slider-dot-mb.active {
            width: 12px;
            height: 12px;
            outline-offset: 2px;
        }

        /* Right Stack Top & Bottom Container CTA Buttons Desktop (Compact) */
        .mb-right_top-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?>,
        .mb-right_bottom-<?= $uniqueId ?> .mb-cta-btn-<?= $uniqueId ?> { padding: 0.32rem 0.85rem; border-top-left-radius: 1rem; }
        .mb-right_top-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?>,
        .mb-right_bottom-<?= $uniqueId ?> .mb-cta-text-<?= $uniqueId ?> { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }

        /* Right Stack Top & Bottom Container Dots Desktop (Small) */
        .mb-right-stack-<?= $uniqueId ?> .slider-dot-mb,
        .mb-right_top-<?= $uniqueId ?> .slider-dot-mb,
        .mb-right_bottom-<?= $uniqueId ?> .slider-dot-mb,
        .mb-right-stack-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet,
        .mb-right_top-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet,
        .mb-right_bottom-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet {
            width: 5px !important;
            height: 5px !important;
            margin: 0 2px !important;
        }
        .mb-right-stack-<?= $uniqueId ?> .slider-dot-mb.active,
        .mb-right_top-<?= $uniqueId ?> .slider-dot-mb.active,
        .mb-right_bottom-<?= $uniqueId ?> .slider-dot-mb.active,
        .mb-right-stack-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet-active,
        .mb-right_top-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet-active,
        .mb-right_bottom-<?= $uniqueId ?> .swiper-pagination-custom .swiper-pagination-bullet-active {
            width: 7px !important;
            height: 7px !important;
            outline-offset: 1.5px !important;
        }
        .mb-right-stack-<?= $uniqueId ?> .mb-dots-container-<?= $uniqueId ?>,
        .mb-right_top-<?= $uniqueId ?> .mb-dots-container-<?= $uniqueId ?>,
        .mb-right_bottom-<?= $uniqueId ?> .mb-dots-container-<?= $uniqueId ?>,
        .mb-right-stack-<?= $uniqueId ?> .swiper-pagination-custom,
        .mb-right_top-<?= $uniqueId ?> .swiper-pagination-custom,
        .mb-right_bottom-<?= $uniqueId ?> .swiper-pagination-custom {
            bottom: 0.625rem !important;
        }
    }
</style>

<?php $mbBg = !empty($contentData['theme']['value']) ? 'background: ' . $contentData['theme']['value'] . ';' : ''; ?>
<div class="w-full relative py-3 sm:py-4 md:py-6">
<section class="w-full max-w-full mx-auto px-1 sm:px-3 md:px-4 lg:px-6">
    <div class="rounded-2xl sm:rounded-[1.5rem] md:rounded-[2rem] lg:rounded-[2.5rem] p-1 sm:p-2 md:p-3 lg:p-3.5 relative" style="<?= $mbBg ?>">
        <?php if ($title): ?>
            <h2 class="text-2xl md:text-3xl font-serif text-gray-900 mb-5 md:mb-8 text-center"><?= htmlspecialchars($title) ?></h2>
        <?php endif; ?>

        <!-- Mobile: single column stacked | Tablet: left big + right stacked | Desktop: 3-col -->
    <div class="mb-grid-wrapper-<?= $uniqueId ?> flex flex-col lg:grid lg:grid-cols-3 gap-4 md:gap-5 lg:gap-6">
        
        <!-- Left Container: full width on mobile, 2/3 on desktop -->
        <?php $left = $sectionsData['left']; ?>
        <div class="mb-left-<?= $uniqueId ?> lg:col-span-2 relative w-full rounded-2xl overflow-hidden shadow-sm flex flex-col group" style="min-height: clamp(220px, 55vw, 640px); transform: translateZ(0); isolation: isolate; -webkit-mask-image: -webkit-radial-gradient(white, black);">
            <?php renderBannerSection($left, 'left', $uniqueId, $dynamicData); ?>
        </div>

        <!-- Right Container Stack: stacked vertically on all screen sizes -->
        <div class="mb-right-stack-<?= $uniqueId ?> lg:col-span-1 flex flex-col gap-4 md:gap-5 lg:gap-6 w-full">
            
            <!-- Right Top Container -->
            <?php $rt = $sectionsData['right_top']; ?>
            <div class="mb-right_top-<?= $uniqueId ?> relative w-full rounded-2xl overflow-hidden shadow-sm flex flex-col group flex-1" style="min-height: clamp(160px, 40vw, 320px); transform: translateZ(0); isolation: isolate; -webkit-mask-image: -webkit-radial-gradient(white, black);">
                <?php renderBannerSection($rt, 'right_top', $uniqueId, $dynamicData); ?>
            </div>

            <!-- Right Bottom Container -->
            <?php $rb = $sectionsData['right_bottom']; ?>
            <div class="mb-right_bottom-<?= $uniqueId ?> relative w-full rounded-2xl overflow-hidden shadow-sm flex flex-col group flex-1" style="min-height: clamp(160px, 40vw, 320px); transform: translateZ(0); isolation: isolate; -webkit-mask-image: -webkit-radial-gradient(white, black);">
                <?php renderBannerSection($rb, 'right_bottom', $uniqueId, $dynamicData); ?>
            </div>

        </div>

        </div>
    </div>
</section>
</div>


<!-- Swiper only needed for product/category sliders in right panels -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($sectionsData as $prefix => $data): 
        if (empty($data)) continue;
        $ct = $data['content_type'] ?? 'custom_image';
        if ($ct === 'custom_image') continue; // handled by inline hero-style JS above
        $animation = $data['animation'] ?? 'fade';
        $effect = $animation === 'fade' ? 'fade' : 'slide';
        $theme = $data['theme'] ?? [];
        $dotColor = $theme['dot_color'] ?? '#8c6a4f';
    ?>
    const swiperEl_<?= $prefix ?>_<?= $uniqueId ?> = document.querySelector('.mySwiper-<?= $prefix ?>-<?= $uniqueId ?>');
    if (swiperEl_<?= $prefix ?>_<?= $uniqueId ?>) {
        const slideCount = swiperEl_<?= $prefix ?>_<?= $uniqueId ?>.querySelectorAll('.swiper-slide').length;
        new Swiper(swiperEl_<?= $prefix ?>_<?= $uniqueId ?>, {
            effect: '<?= $effect ?>',
            fadeEffect: { crossFade: true },
            loop: slideCount > 1,
            autoplay: slideCount > 1 ? { delay: 4000, disableOnInteraction: false } : false,
            navigation: {
                nextEl: '.mySwiper-<?= $prefix ?>-<?= $uniqueId ?> .swiper-button-next-custom',
                prevEl: '.mySwiper-<?= $prefix ?>-<?= $uniqueId ?> .swiper-button-prev-custom',
            },
            pagination: {
                el: '.mySwiper-<?= $prefix ?>-<?= $uniqueId ?> .swiper-pagination-custom',
                clickable: true,
                bulletClass: 'swiper-pagination-bullet',
                bulletActiveClass: 'swiper-pagination-bullet-active',
                renderBullet: function (index, className) {
                    return '<button class="' + className + '" style="background-color: <?= $dotColor ?> !important; outline-color: <?= $dotColor ?> !important;" aria-label="Slide ' + (index + 1) + '"></button>';
                }
            }
        });
    }
    <?php endforeach; ?>
});
</script>
