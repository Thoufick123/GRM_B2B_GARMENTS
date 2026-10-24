<?php
/**
 * Dynamic Reels Container Component for Storefront Landing Page
 * 100% dynamic - Zero hardcoding
 */
$reelsData = $contentData ?? [];
$items = !empty($reelsData['items']) && is_array($reelsData['items']) ? $reelsData['items'] : [];

if (empty($items)) {
    return; // Don't render empty container
}

$title = $reelsData['title'] ?? '';
$subtitle = $reelsData['subtitle'] ?? '';
$deskHeight = !empty($reelsData['desktop_height_vh']) ? (float)$reelsData['desktop_height_vh'] : 70;
$mobHeight = !empty($reelsData['mobile_height_vh']) ? (float)$reelsData['mobile_height_vh'] : 55;
$deskVal = $reelsData['desktop_count'] ?? $reelsData['desktop_view_count'] ?? $reelsData['cards_desktop'] ?? 4;
$tabVal  = $reelsData['tablet_view_count'] ?? $reelsData['cards_tablet'] ?? 3;
$mobVal  = $reelsData['mobile_count'] ?? $reelsData['mobile_view_count'] ?? $reelsData['cards_mobile'] ?? 1;

$deskCols = max(1, min(10, (float)$deskVal));
$tabCols  = max(1, min(8, (float)$tabVal));
$mobCols  = max(1, min(6, (float)$mobVal));
$autoplay = !empty($reelsData['autoplay']);
$isMuted = !empty($reelsData['muted']);
$isLoop = !empty($reelsData['loop']);
$bgColor = !empty($reelsData['bg_color']) ? $reelsData['bg_color'] : '#ffffff';
$uniqueId = 'reels_' . substr(md5(uniqid()), 0, 8);
?>

<style>
/* Dynamic Responsive Viewport Heights & Column Counts */
#<?= $uniqueId ?> {
    --reel-gap: 0.875rem;
    --mob-cols: <?= $mobCols ?>;
    --tab-cols: <?= $tabCols ?>;
    --desk-cols: <?= $deskCols ?>;
}
#<?= $uniqueId ?> .reels-track {
    display: flex !important;
    gap: var(--reel-gap) !important;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
#<?= $uniqueId ?> .reels-track::-webkit-scrollbar {
    display: none;
}
#<?= $uniqueId ?> .reel-card {
    height: <?= max(58, (float)$mobHeight) ?>vh;
    min-height: 440px;
    max-height: 620px;
    width: calc((100% - ((var(--mob-cols) - 1) * var(--reel-gap))) / var(--mob-cols)) !important;
    max-width: calc((100% - ((var(--mob-cols) - 1) * var(--reel-gap))) / var(--mob-cols)) !important;
    min-width: 0 !important;
    flex: 0 0 calc((100% - ((var(--mob-cols) - 1) * var(--reel-gap))) / var(--mob-cols)) !important;
    box-sizing: border-box !important;
    scroll-snap-align: start;
}
@media (min-width: 640px) and (max-width: 1023px) {
    #<?= $uniqueId ?> {
        --reel-gap: 1.125rem;
    }
    #<?= $uniqueId ?> .reel-card {
        height: <?= max($mobHeight, round(($mobHeight + $deskHeight) / 2)) ?>vh;
        width: calc((100% - ((var(--tab-cols) - 1) * var(--reel-gap))) / var(--tab-cols)) !important;
        max-width: calc((100% - ((var(--tab-cols) - 1) * var(--reel-gap))) / var(--tab-cols)) !important;
        flex: 0 0 calc((100% - ((var(--tab-cols) - 1) * var(--reel-gap))) / var(--tab-cols)) !important;
    }
}
@media (min-width: 1024px) {
    #<?= $uniqueId ?> {
        --reel-gap: 1.25rem;
    }
    #<?= $uniqueId ?> .reel-card {
        height: <?= $deskHeight ?>vh;
        min-height: 400px;
        max-height: 750px;
        width: calc((100% - ((var(--desk-cols) - 1) * var(--reel-gap))) / var(--desk-cols)) !important;
        max-width: calc((100% - ((var(--desk-cols) - 1) * var(--reel-gap))) / var(--desk-cols)) !important;
        flex: 0 0 calc((100% - ((var(--desk-cols) - 1) * var(--reel-gap))) / var(--desk-cols)) !important;
        <?= count($items) === 1 ? 'max-width: 380px !important; width: 100% !important;' : '' ?>
    }
}
</style>

<section id="<?= $uniqueId ?>" class="py-3 sm:py-5 md:py-6 relative overflow-hidden" style="background-color: <?= htmlspecialchars($bgColor) ?>;">
    <div class="w-full px-3 sm:px-5 lg:px-6">
        
        <!-- Header with Mobile Compact Navigation Arrows -->
        <?php if (!empty($title) || !empty($subtitle)): ?>
            <div class="mb-3 sm:mb-6 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <?php if (!empty($title)): ?>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#F25996] animate-pulse shrink-0"></span>
                            <h2 class="text-xl sm:text-3xl font-display font-extrabold text-gray-900 tracking-tight">
                                <?= htmlspecialchars($title) ?>
                            </h2>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($subtitle)): ?>
                        <p class="text-xs sm:text-sm text-gray-500 font-medium max-w-2xl">
                            <?= htmlspecialchars($subtitle) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (count($items) > 1): ?>
                    <!-- Mobile Compact Header Arrows (Never blocks the reels!) -->
                    <div class="flex sm:hidden items-center gap-1.5 shrink-0">
                        <button type="button" 
                                id="reels-mob-prev-<?= $uniqueId ?>"
                                onclick="scrollReelsTrack('<?= $uniqueId ?>', -1)" 
                                class="w-8 h-8 rounded-full bg-white text-gray-700 shadow-sm border border-gray-200 flex items-center justify-center active:scale-95 transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none"
                                aria-label="Previous Reel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button type="button" 
                                id="reels-mob-next-<?= $uniqueId ?>"
                                onclick="scrollReelsTrack('<?= $uniqueId ?>', 1)" 
                                class="w-8 h-8 rounded-full bg-white text-gray-700 shadow-sm border border-gray-200 flex items-center justify-center active:scale-95 transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none"
                                aria-label="Next Reel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Track Wrapper with Desktop Navigation Arrows -->
        <div class="relative px-0.5 sm:px-14">
            
            <?php if (count($items) > 1): ?>
                <!-- Left Nav Arrow Button (Desktop Only - Outside Reels) -->
                <button type="button" 
                        id="reels-prev-<?= $uniqueId ?>"
                        onclick="scrollReelsTrack('<?= $uniqueId ?>', -1)" 
                        class="hidden sm:flex absolute left-0 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white text-gray-800 shadow-xl border border-gray-200 items-center justify-center transition-all duration-300 hover:bg-gray-50 hover:scale-110 active:scale-95 focus:outline-none disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none"
                        aria-label="Previous Reels">
                    <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </button>
            <?php endif; ?>

            <!-- Horizontal Scrollable Snap Track with unrestricted touch scrolling -->
            <div class="reels-track flex items-stretch overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 pt-1 <?= count($items) < $deskCols ? 'justify-start lg:justify-center' : 'justify-start' ?>" id="track-<?= $uniqueId ?>" style="touch-action: pan-x pan-y; -webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
                <?php foreach ($items as $idx => $item): 
                    $reelTitle = !empty($item['title']) ? $item['title'] : 'Explore Style';
                    $ctaUrl = !empty($item['cta_url']) ? $item['cta_url'] : '#';
                    $ctaText = !empty($item['cta_text']) ? $item['cta_text'] : 'Shop Now';
                    $deskImg = !empty($item['desktop_image']) ? $item['desktop_image'] : '';
                    $mobImg = !empty($item['mobile_image']) ? $item['mobile_image'] : $deskImg;
                    $videoUrl = !empty($item['video_url']) ? $item['video_url'] : '';
                    $finalCtaUrl = (!empty($ctaUrl) && $ctaUrl !== '#') 
                        ? ((str_starts_with($ctaUrl, 'http://') || str_starts_with($ctaUrl, 'https://')) ? $ctaUrl : (rtrim(BASE_URL, '/') . '/' . ltrim($ctaUrl, '/'))) 
                        : '';
                ?>
                    <div class="reel-card relative rounded-2xl sm:rounded-3xl overflow-hidden bg-gray-950 shadow-md group cursor-pointer transition-all duration-300 hover:shadow-2xl hover:-translate-y-1"
                         <?= !empty($finalCtaUrl) ? 'onclick="window.location.href=\'' . htmlspecialchars($finalCtaUrl, ENT_QUOTES) . '\'"' : '' ?>
                         data-video="<?= htmlspecialchars($videoUrl) ?>"
                         data-title="<?= htmlspecialchars($reelTitle) ?>"
                         data-cta-url="<?= htmlspecialchars($finalCtaUrl ?: $ctaUrl) ?>"
                         data-cta-text="<?= htmlspecialchars($ctaText) ?>">
                        
                        <!-- 24-Hour Looping Video Player (Preload none avoids massive initial payload) -->
                        <?php 
                            $optDeskImg = get_image_url($deskImg, 400, 700);
                            $optMobImg = get_image_url($mobImg ?: $deskImg, 300, 500);
                        ?>
                        <?php if (!empty($videoUrl)): ?>
                            <video class="reel-bg-video w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                   src="<?= htmlspecialchars($videoUrl) ?>"
                                   <?= $autoplay ? 'autoplay' : '' ?>
                                   <?= $isLoop ? 'loop' : '' ?>
                                   <?= $isMuted ? 'muted' : '' ?>
                                   playsinline
                                   webkit-playsinline
                                   preload="none"
                                   poster="<?= htmlspecialchars($optDeskImg ?: $optMobImg) ?>">
                            </video>
                        <?php elseif (!empty($deskImg) || !empty($mobImg)): ?>
                            <picture class="w-full h-full block">
                                <?php if (!empty($optDeskImg)): ?>
                                    <source media="(min-width: 768px)" srcset="<?= htmlspecialchars($optDeskImg) ?>">
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($optMobImg ?: $optDeskImg) ?>" alt="<?= htmlspecialchars($reelTitle) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" width="300" height="500">
                            </picture>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-900 text-gray-600">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                        <?php endif; ?>

                        <!-- Overlay Gradients -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-black/30 pointer-events-none transition-opacity group-hover:opacity-90"></div>

                        <!-- Top Badge (Reel Icon & Play indicator) -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-black/50 backdrop-blur-md text-white border border-white/20">
                                <svg class="w-3 h-3 text-[#F25996]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                                Reel
                            </span>
                            <?php if (!empty($videoUrl)): ?>
                                <button type="button" onclick="toggleReelCardSound(event, this)" class="pointer-events-auto w-7 h-7 rounded-full bg-black/50 backdrop-blur-md text-white flex items-center justify-center border border-white/20 hover:bg-black/70 transition-all" title="Toggle Sound">
                                    <svg class="w-3.5 h-3.5 sound-icon-muted <?= $isMuted ? '' : 'hidden' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
                                    <svg class="w-3.5 h-3.5 sound-icon-unmuted <?= $isMuted ? 'hidden' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Bottom Content & CTA -->
                        <div class="absolute bottom-0 inset-x-0 p-4 pb-5 sm:p-5 flex flex-col justify-end text-white pointer-events-none z-20">
                            <h3 class="font-display font-bold text-sm sm:text-base leading-snug line-clamp-2 drop-shadow-md mb-2">
                                <?= htmlspecialchars($reelTitle) ?>
                            </h3>
                            
                            <?php if (!empty($finalCtaUrl)): ?>
                                <a href="<?= htmlspecialchars($finalCtaUrl) ?>" 
                                   onclick="event.stopPropagation(); window.location.href=this.href;" 
                                   class="pointer-events-auto relative z-30 inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-3 rounded-xl bg-white/95 hover:bg-white text-gray-900 font-bold text-xs shadow-lg backdrop-blur-sm transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                                    <span><?= htmlspecialchars($ctaText) ?></span>
                                    <svg class="w-3.5 h-3.5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($items) > 1): ?>
                <!-- Right Nav Arrow Button (Desktop Only - Outside Reels) -->
                <button type="button" 
                        id="reels-next-<?= $uniqueId ?>"
                        onclick="scrollReelsTrack('<?= $uniqueId ?>', 1)" 
                        class="hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white text-gray-800 shadow-xl border border-gray-200 items-center justify-center transition-all duration-300 hover:bg-gray-50 hover:scale-110 active:scale-95 focus:outline-none disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none"
                        aria-label="Next Reels">
                    <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                </button>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
window.reelsComponentsData = window.reelsComponentsData || {};
window.reelsComponentsData['<?= $uniqueId ?>'] = {
    items: <?= json_encode($items) ?>,
    currentIndex: 0,
    autoplay: <?= $autoplay ? 'true' : 'false' ?>
};

function getReelsCardStep(track) {
    const cards = track.querySelectorAll('.reel-card');
    if (cards.length > 1) {
        return cards[1].offsetLeft - cards[0].offsetLeft;
    }
    return cards.length === 1 ? cards[0].offsetWidth : (track.clientWidth * 0.85);
}

function updateReelsLock(containerId) {
    const track = document.getElementById(`track-${containerId}`);
    const prevBtn = document.getElementById(`reels-prev-${containerId}`);
    const nextBtn = document.getElementById(`reels-next-${containerId}`);
    const mobPrevBtn = document.getElementById(`reels-mob-prev-${containerId}`);
    const mobNextBtn = document.getElementById(`reels-mob-next-${containerId}`);
    if (!track) return;
    
    const isAtStart = track.scrollLeft <= 5;
    const isAtEnd = (track.scrollLeft + track.clientWidth) >= (track.scrollWidth - 8);
    
    [prevBtn, mobPrevBtn].forEach(btn => {
        if (btn) {
            btn.disabled = isAtStart;
            btn.style.opacity = isAtStart ? '0.35' : '1';
            btn.style.cursor = isAtStart ? 'not-allowed' : 'pointer';
            btn.style.pointerEvents = isAtStart ? 'none' : 'auto';
        }
    });
    [nextBtn, mobNextBtn].forEach(btn => {
        if (btn) {
            btn.disabled = isAtEnd;
            btn.style.opacity = isAtEnd ? '0.35' : '1';
            btn.style.cursor = isAtEnd ? 'not-allowed' : 'pointer';
            btn.style.pointerEvents = isAtEnd ? 'none' : 'auto';
        }
    });
}

function scrollReelsTrack(containerId, direction) {
    const track = document.getElementById(`track-${containerId}`);
    if (track) {
        const step = getReelsCardStep(track);
        track.scrollBy({ left: step * direction, behavior: 'smooth' });
        setTimeout(() => updateReelsLock(containerId), 150);
        setTimeout(() => updateReelsLock(containerId), 400);
    }
}

function toggleReelCardSound(e, btn) {
    e.stopPropagation();
    const card = btn.closest('.reel-card');
    const video = card ? card.querySelector('video') : null;
    if (video) {
        video.muted = !video.muted;
        const muteIcon = btn.querySelector('.sound-icon-muted');
        const unmuteIcon = btn.querySelector('.sound-icon-unmuted');
        if (muteIcon && unmuteIcon) {
            muteIcon.classList.toggle('hidden', !video.muted);
            unmuteIcon.classList.toggle('hidden', video.muted);
        }
        if (video.paused) {
            video.play().catch(() => {});
        }
    }
}

// 24-Hour Continuous Non-Stop Looping Video Engine
function init24HrVideoLoop_<?= $uniqueId ?>() {
    const track = document.getElementById('track-<?= $uniqueId ?>');
    if (!track) return;
    updateReelsLock('<?= $uniqueId ?>');
    track.addEventListener('scroll', () => updateReelsLock('<?= $uniqueId ?>'), { passive: true });
    window.addEventListener('resize', () => updateReelsLock('<?= $uniqueId ?>'));
    const cards = track.querySelectorAll('.reel-card');
    
    // IntersectionObserver to ensure playback starts as soon as card is in viewport
    const observer = ('IntersectionObserver' in window) ? new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const v = entry.target.querySelector('video.reel-bg-video');
            if (v && entry.isIntersecting) {
                v.muted = true;
                v.defaultMuted = true;
                v.play().catch(() => {});
            }
        });
    }, { threshold: 0.15 }) : null;

    cards.forEach(card => {
        const video = card.querySelector('video.reel-bg-video');
        if (!video) return;

        const isMutedSetting = <?= $isMuted ? 'true' : 'false' ?>;
        const isLoopSetting = <?= $isLoop ? 'true' : 'false' ?>;
        const isAutoplaySetting = <?= $autoplay ? 'true' : 'false' ?>;

        video.muted = isMutedSetting;
        video.defaultMuted = isMutedSetting;
        video.loop = isLoopSetting;
        video.playsInline = true;
        if (isMutedSetting) video.setAttribute('muted', '');
        if (isLoopSetting) video.setAttribute('loop', '');
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');
        if (isAutoplaySetting) video.setAttribute('autoplay', '');

        const startPlayback = () => {
            if (isMutedSetting) {
                video.muted = true;
                video.defaultMuted = true;
            }
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(() => {
                    video.muted = true;
                    video.play().catch(() => {});
                });
            }
        };

        // Start playing immediately on page load
        startPlayback();

        if (observer) {
            observer.observe(card);
        }

        // Hover or touch on card triggers playback
        card.addEventListener('mouseenter', startPlayback);
        card.addEventListener('touchstart', startPlayback, { passive: true });

        // 24-hour continuous loop guarantee: immediately restart if video ends
        video.addEventListener('ended', () => {
            video.currentTime = 0;
            video.play().catch(() => {});
        });

        // If paused by browser battery saver or OS, resume looping automatically
        video.addEventListener('pause', () => {
            if (!document.hidden) {
                video.play().catch(() => {});
            }
        });
    });

    // One-time kickstarter on first user scroll or interaction
    const kickstart = () => {
        cards.forEach(card => {
            const v = card.querySelector('video.reel-bg-video');
            if (v && v.paused) {
                v.muted = true;
                v.play().catch(() => {});
            }
        });
    };
    window.addEventListener('scroll', kickstart, { once: true, passive: true });
    window.addEventListener('click', kickstart, { once: true });
    window.addEventListener('touchstart', kickstart, { once: true, passive: true });

    // Track Auto-Scroll Carousel Engine (when autoplay is enabled in CMS)
    const isAutoplay = <?= $autoplay ? 'true' : 'false' ?>;
    if (isAutoplay && cards.length > 1) {
        let autoScrollInterval = null;

        const startAutoScroll = () => {
            if (autoScrollInterval) return;
            autoScrollInterval = setInterval(() => {
                const step = getReelsCardStep(track);
                const maxScroll = track.scrollWidth - track.clientWidth;

                if (track.scrollLeft >= maxScroll - 10) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: step, behavior: 'smooth' });
                }
            }, 3500);
        };

        const stopAutoScroll = () => {
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }
        };

        // Pause auto-scroll on hover or touch, resume on leave
        track.addEventListener('mouseenter', stopAutoScroll);
        track.addEventListener('mouseleave', startAutoScroll);
        // Attach scroll and resize listeners for dynamic left & right scroll lock
        updateReelsLock('<?= $uniqueId ?>');
        track.addEventListener('scroll', () => updateReelsLock('<?= $uniqueId ?>'), { passive: true });
        window.addEventListener('resize', () => updateReelsLock('<?= $uniqueId ?>'), { passive: true });

        startAutoScroll();
    } else {
        // Even when no autoplay, initialize the boundary lock and listeners
        updateReelsLock('<?= $uniqueId ?>');
        track.addEventListener('scroll', () => updateReelsLock('<?= $uniqueId ?>'), { passive: true });
        window.addEventListener('resize', () => updateReelsLock('<?= $uniqueId ?>'), { passive: true });
    }
}

// Ensure execution even if DOMContentLoaded already fired
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init24HrVideoLoop_<?= $uniqueId ?>);
} else {
    init24HrVideoLoop_<?= $uniqueId ?>();
}
window.addEventListener('load', init24HrVideoLoop_<?= $uniqueId ?>);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        init24HrVideoLoop_<?= $uniqueId ?>();
    }
});
</script>
