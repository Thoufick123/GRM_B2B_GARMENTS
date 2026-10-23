<?php
// $contentData is injected by home.php from the cms_components table
$title       = $contentData['title'] ?? '';
$subtitle    = $contentData['subtitle'] ?? '';
$buttonText  = $contentData['button_text'] ?? 'Shop Now';
$ctaUrl      = $contentData['cta_url'] ?? '';
$animation   = $contentData['animation'] ?? 'fade';
$showArrows  = $contentData['show_arrows'] ?? true;
$showDots    = $contentData['show_dots'] ?? true;

$arrowColor  = $contentData['theme']['arrow_color'] ?? '#000000';
$arrowBgColor = $contentData['theme']['arrow_bg_color'] ?? '#ffffff';
$dotColor    = $contentData['theme']['dot_color'] ?? '#8c6a4f';
$titleColor  = $contentData['theme']['title_color'] ?? '#ffffff';
$subtitleColor = $contentData['theme']['subtitle_color'] ?? '#ffffff';
$descriptionColor = $contentData['theme']['description_color'] ?? '#ffffff';
$buttonBgColor = $contentData['theme']['button_bg_color'] ?? '#111827';
$buttonTextColor = $contentData['theme']['button_text_color'] ?? '#ffffff';

// The dots are styled purely with inline CSS below.

$themeValue  = $contentData['theme']['value'] ?? '';
$mediaItems  = $contentData['media'] ?? [];
$mediaItems  = array_values(array_filter($mediaItems, function($m) { 
    return !empty(trim($m['desktop'] ?? '')) || !empty(trim($m['mobile'] ?? '')); 
}));
$description = $contentData['description'] ?? '';

$containerHeight = !empty($contentData['container_height']) ? $contentData['container_height'] : '80';
if (is_numeric($containerHeight)) {
    $containerHeight .= 'vh';
}

$mobileContainerHeight = !empty($contentData['mobile_container_height']) ? $contentData['mobile_container_height'] : '100';
if (is_numeric($mobileContainerHeight)) {
    $mobileContainerHeight .= 'vh';
}



$imageFit = $contentData['image_fit'] ?? 'cover';
// Use inline styles because object-contain and object-fill are not in the compiled Tailwind CSS
$fitStyle = "object-fit: " . htmlspecialchars($imageFit) . ";";

if ($ctaUrl && !str_starts_with($ctaUrl, 'http')) {
    $ctaUrl = BASE_URL . '/' . ltrim($ctaUrl, '/');
}

$id = uniqid('slider_');
?>
<?php 
$hbBg = '';
if ($themeValue) {
    // If user pasted full CSS rules (e.g., from a generator), use it directly
    if (str_contains($themeValue, 'background:') || str_contains($themeValue, 'background-image:')) {
        $hbBg = $themeValue;
        // Ensure it ends with a semicolon
        if (!str_ends_with(trim($hbBg), ';')) {
            $hbBg .= ';';
        }
    } else {
        $hbBg = 'background: ' . $themeValue . ';';
    }
}
?>
<div class="w-full relative py-3 sm:py-4 md:py-6" id="<?= $id ?>">
    <style>
        #<?= $id ?> .hero-banner-inner {
            height: <?= htmlspecialchars($mobileContainerHeight) ?>;
            min-height: 280px;
        }
        #<?= $id ?> .hero-cta-btn {
            border-top-left-radius: 1.15rem;
            padding: 0.4rem 0.95rem;
        }
        #<?= $id ?> .hero-cta-text { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        #<?= $id ?> .hero-dots-container { bottom: 0.625rem; }
        #<?= $id ?> .slider-dot {
            width: 6px;
            height: 6px;
            background-color: <?= htmlspecialchars($dotColor) ?> !important;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: all 0.3s ease;
            opacity: 0.5;
            display: inline-block;
            vertical-align: middle;
        }
        #<?= $id ?> .slider-dot.active {
            width: 8px;
            height: 8px;
            background-color: <?= htmlspecialchars($dotColor) ?> !important;
            opacity: 1;
            outline: 2px solid <?= htmlspecialchars($dotColor) ?> !important;
            outline-offset: 2px;
        }
        @media (min-width: 768px) {
            #<?= $id ?> .hero-banner-inner {
                height: <?= htmlspecialchars($containerHeight) ?>;
                min-height: 400px;
            }
            #<?= $id ?> .hero-cta-btn {
                border-top-left-radius: 1.5rem;
                padding: 0.6rem 1.45rem;
            }
            #<?= $id ?> .hero-cta-text { font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
            #<?= $id ?> .hero-dots-container { bottom: 1.25rem; }
            #<?= $id ?> .slider-dot {
                width: 10px;
                height: 10px;
            }
            #<?= $id ?> .slider-dot.active {
                width: 12px;
                height: 12px;
                outline: 2px solid <?= htmlspecialchars($dotColor) ?> !important;
                outline-offset: 2px;
            }
        }
        @media (min-width: 1024px) {
            #<?= $id ?> .hero-cta-btn {
                border-top-left-radius: 1.85rem;
                padding: 0.85rem 2.15rem;
            }
            #<?= $id ?> .hero-cta-text {
                font-size: 1.05rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }
            #<?= $id ?> .slider-prev,
            #<?= $id ?> .slider-next {
                width: 2.75rem !important;
                height: 2.75rem !important;
            }
            #<?= $id ?> .slider-prev {
                left: 1.25rem;
            }
            #<?= $id ?> .slider-next {
                right: 1.25rem;
            }
            #<?= $id ?> .slider-prev svg,
            #<?= $id ?> .slider-next svg {
                width: 1.25rem !important;
                height: 1.25rem !important;
            }
        }
    </style>
    <div class="w-full max-w-full mx-auto px-1 sm:px-3 md:px-4 lg:px-6">
        <div class="rounded-2xl sm:rounded-[1.5rem] md:rounded-[2rem] lg:rounded-[2.5rem] p-1 sm:p-2 md:p-3 lg:p-3.5 relative" style="<?= htmlspecialchars($hbBg) ?>">
            <div class="hero-banner-inner w-full rounded-xl sm:rounded-[1.25rem] md:rounded-[1.75rem] overflow-hidden relative shadow-sm border border-gray-200 group" style="transform: translateZ(0); isolation: isolate; -webkit-mask-image: -webkit-radial-gradient(white, black);">
        
        <div class="slider-track w-full h-full relative">
            <?php foreach ($mediaItems as $index => $media): 
                $dRaw = trim((string)($media['desktop'] ?? ''));
                $mRaw = trim((string)($media['mobile'] ?? ''));
                if ($dRaw === '' && $mRaw !== '') $dRaw = $mRaw;
                if ($mRaw === '' && $dRaw !== '') $mRaw = $dRaw;
                if (!$dRaw) continue;

                $dUrl = get_image_url($dRaw, 1920, 900);
                $mUrl = get_image_url($mRaw, 900, 900);

                $overlayOpacity = isset($media['opacity']) && $media['opacity'] !== '' ? (float)$media['opacity'] : 0;
                $overlayFrac = $overlayOpacity > 1 ? ($overlayOpacity / 100) : $overlayOpacity;
                $overlayFrac = max(0, min(1, $overlayFrac));

                $defaultType = $media['type'] ?? 'image';
                $checkIsVideo = function($url) use ($defaultType) {
                    if (empty($url)) return false;
                    if (preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $url) || str_contains($url, '/video/upload/')) return true;
                    if (preg_match('/\.(jpg|jpeg|png|webp|gif|svg|avif)(\?.*)?$/i', $url) || str_contains($url, '/image/upload/')) return false;
                    return $defaultType === 'video';
                };

                $dIsVideo = $checkIsVideo($dRaw);
                $mIsVideo = $checkIsVideo($mRaw);
                $isSameMedia = ($dRaw === $mRaw && $dIsVideo === $mIsVideo);
                $isLcp = ($index === 0);
            ?>
                <div class="slide absolute inset-0 w-full h-full transition-all duration-700 ease-in-out <?= $index === 0 ? 'opacity-100 z-1' : 'opacity-0 z-0' ?>" data-index="<?= $index ?>">
                    <?php if ($isSameMedia): ?>
                        <?php if ($dIsVideo): ?>
                            <video class="w-full h-full" style="<?= $fitStyle ?>" autoplay muted loop playsinline src="<?= htmlspecialchars($dUrl) ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($dUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full h-full" style="<?= $fitStyle ?>" loading="<?= $isLcp ? 'eager' : 'lazy' ?>" <?= $isLcp ? 'fetchpriority="high"' : '' ?> decoding="async" width="1600" height="600">
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Desktop Media (>= 768px) -->
                        <?php if ($dIsVideo): ?>
                            <video class="hidden md:block w-full h-full" style="<?= $fitStyle ?>" autoplay muted loop playsinline src="<?= htmlspecialchars($dUrl) ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($dUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="hidden md:block w-full h-full" style="<?= $fitStyle ?>" loading="<?= $isLcp ? 'eager' : 'lazy' ?>" <?= $isLcp ? 'fetchpriority="high"' : '' ?> decoding="async" width="1600" height="600">
                        <?php endif; ?>

                        <!-- Mobile Media (< 768px) -->
                        <?php if ($mIsVideo): ?>
                            <video class="block md:hidden w-full h-full" style="<?= $fitStyle ?>" autoplay muted loop playsinline src="<?= htmlspecialchars($mUrl) ?>"></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($mUrl) ?>" alt="<?= htmlspecialchars($title) ?>" class="block md:hidden w-full h-full" style="<?= $fitStyle ?>" loading="<?= $isLcp ? 'eager' : 'lazy' ?>" <?= $isLcp ? 'fetchpriority="high"' : '' ?> decoding="async" width="900" height="600">
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($overlayFrac > 0): ?>
                        <div class="absolute inset-0 pointer-events-none" style="background-color: rgba(0, 0, 0, <?= $overlayFrac ?>);"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if(empty($mediaItems)): ?>
                <!-- Fallback if no media -->
                <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">No media found</div>
            <?php endif; ?>
        </div>

        <!-- Overlay Text -->
        <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4 sm:px-8 lg:px-16 pt-2 pb-12 sm:pb-14 md:py-0 z-20 pointer-events-none">
            <div class="max-w-[270px] sm:max-w-md md:max-w-2xl lg:max-w-3xl flex flex-col items-center">
                <?php if ($subtitle): ?>
                    <p class="text-[9px] sm:text-xs md:text-base tracking-[0.15em] md:tracking-[0.2em] font-bold mb-1 sm:mb-2 md:mb-4 uppercase drop-shadow-sm leading-tight" style="color: <?= htmlspecialchars($subtitleColor) ?>;">
                        <?= htmlspecialchars($subtitle) ?>
                    </p>
                <?php endif; ?>
                
                <?php if ($title): ?>
                    <h2 class="text-lg sm:text-2xl md:text-5xl lg:text-6xl font-black mb-1 sm:mb-2 md:mb-6 drop-shadow-md leading-tight tracking-tight" style="color: <?= htmlspecialchars($titleColor) ?>;">
                        <?= htmlspecialchars($title) ?>
                    </h2>
                <?php endif; ?>
                <?php if ($description): ?>
                    <p class="text-[11px] sm:text-sm md:text-base lg:text-lg font-medium drop-shadow-sm leading-tight sm:leading-normal" style="color: <?= htmlspecialchars($descriptionColor) ?>; word-break: break-word; overflow-wrap: break-word;">
                        <?= nl2br(htmlspecialchars($description)) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Call to Action -->
        <?php if (!empty($buttonText)): ?>
        <a href="<?= htmlspecialchars($ctaUrl ?: '#') ?>" class="hero-cta-btn absolute bottom-0 right-0 pointer-events-auto flex items-center justify-center hover:opacity-90 transition-opacity group/btn shadow-xl" style="z-index: 30; background-color: <?= htmlspecialchars($buttonBgColor) ?>; color: <?= htmlspecialchars($buttonTextColor) ?>;">
            <span class="hero-cta-text font-bold tracking-wide"><?= htmlspecialchars($buttonText) ?></span>
        </a>
        <?php endif; ?>

        <!-- Navigation Arrows (Controlled by CMS Settings) -->
        <?php if ($showArrows && !empty($mediaItems)): ?>
        <button class="slider-prev absolute left-2 sm:left-3 md:left-4 top-1/2 -translate-y-1/2 w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all z-30 focus:outline-none" style="color: <?= htmlspecialchars($arrowColor) ?>; background-color: <?= htmlspecialchars($arrowBgColor) ?>;" aria-label="Previous Slide">
            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button class="slider-next absolute right-2 sm:right-3 md:right-4 top-1/2 -translate-y-1/2 w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all z-30 focus:outline-none" style="color: <?= htmlspecialchars($arrowColor) ?>; background-color: <?= htmlspecialchars($arrowBgColor) ?>;" aria-label="Next Slide">
            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <?php endif; ?>

        <!-- Pagination Dots -->
        <?php if ($showDots): ?>
        <div class="hero-dots-container absolute left-1/2 -translate-x-1/2 flex items-center space-x-2 sm:space-x-3 md:space-x-4" style="z-index: 30;">
            <?php foreach (empty($mediaItems) ? [1] : $mediaItems as $index => $media): ?>
                <button class="slider-dot transition-all duration-300 rounded-full <?= $index === 0 ? 'active' : '' ?>" style="background-color: <?= htmlspecialchars($dotColor) ?> !important; <?= $index === 0 ? 'outline: 2px solid ' . htmlspecialchars($dotColor) . ' !important; outline-offset: 2px; opacity: 1;' : 'outline: none !important; opacity: 0.5;' ?>" data-index="<?= $index ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('<?= $id ?>');
    if(!container) return;

    const slides = container.querySelectorAll('.slide');
    const dots = container.querySelectorAll('.slider-dot');
    const prevBtn = container.querySelector('.slider-prev');
    const nextBtn = container.querySelector('.slider-next');
    let currentIndex = 0;
    const total = slides.length || 1;
    const animation = '<?= $animation ?>'; // fade, slide_left, slide_right, zoom
    let autoPlay;

    function updateSlider(newIndex, isNext) {
        if (total <= 1 || newIndex === currentIndex) return;
        const currentSlide = slides[currentIndex];
        const nextSlide = slides[newIndex];

        // Reset classes
        slides.forEach(s => {
            s.style.transition = 'all 0.7s ease-in-out';
            s.style.zIndex = '0';
            if(s !== currentSlide && s !== nextSlide) {
                s.style.opacity = '0';
                s.style.transform = 'translateX(0) scale(1)';
            }
        });

        currentSlide.style.zIndex = '1';
        nextSlide.style.zIndex = '2';
        nextSlide.style.opacity = '1';

        // Apply animation
        if (animation === 'fade') {
            currentSlide.style.opacity = '0';
        } else if (animation === 'slide_left') {
            currentSlide.style.transform = isNext ? 'translateX(-100%)' : 'translateX(100%)';
            nextSlide.style.transform = isNext ? 'translateX(100%)' : 'translateX(-100%)';
            // Force reflow
            void nextSlide.offsetWidth;
            nextSlide.style.transform = 'translateX(0)';
            setTimeout(() => { currentSlide.style.opacity = '0'; currentSlide.style.transform = 'translateX(0)'; }, 700);
        } else if (animation === 'slide_right') {
            currentSlide.style.transform = isNext ? 'translateX(100%)' : 'translateX(-100%)';
            nextSlide.style.transform = isNext ? 'translateX(-100%)' : 'translateX(100%)';
            void nextSlide.offsetWidth;
            nextSlide.style.transform = 'translateX(0)';
            setTimeout(() => { currentSlide.style.opacity = '0'; currentSlide.style.transform = 'translateX(0)'; }, 700);
        } else if (animation === 'zoom') {
            currentSlide.style.opacity = '0';
            currentSlide.style.transform = 'scale(1.1)';
            nextSlide.style.transform = 'scale(0.9)';
            void nextSlide.offsetWidth;
            nextSlide.style.transform = 'scale(1)';
            setTimeout(() => { currentSlide.style.transform = 'scale(1)'; }, 700);
        }

        // Update dots
        dots.forEach((d, i) => {
            if (i === newIndex) {
                d.classList.add('active');
                d.style.opacity = '1';
                d.style.backgroundColor = '<?= htmlspecialchars($dotColor) ?>';
                d.style.outline = '2px solid <?= htmlspecialchars($dotColor) ?>';
                d.style.outlineOffset = '2px';
                d.onmouseover = null;
                d.onmouseout = null;
            } else {
                d.classList.remove('active');
                d.style.opacity = '0.5';
                d.style.backgroundColor = '<?= htmlspecialchars($dotColor) ?>';
                d.style.outline = 'none';
                d.onmouseover = function() { this.style.opacity = '0.8'; };
                d.onmouseout = function() { this.style.opacity = '0.5'; };
            }
        });

        currentIndex = newIndex;
        resetTimer();
    }

    function next() {
        const nextIdx = (currentIndex + 1) % total;
        updateSlider(nextIdx, true);
    }

    function prev() {
        const prevIdx = (currentIndex - 1 + total) % total;
        updateSlider(prevIdx, false);
    }

    function resetTimer() {
        clearInterval(autoPlay);
        autoPlay = setInterval(next, 5000);
    }

    if(nextBtn) nextBtn.addEventListener('click', next);
    if(prevBtn) prevBtn.addEventListener('click', prev);
    
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            const isNext = idx > currentIndex;
            updateSlider(idx, isNext);
        });
    });

    resetTimer(); // Start autoplay
});
</script>