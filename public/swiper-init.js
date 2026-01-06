/**
 * YouTube Shorts Swiper - Frontend Script
 */
(function() {
    'use strict';

    // DOM hazır olduğunda başlat
    document.addEventListener('DOMContentLoaded', function() {
        initYTShortsSwipers();
    });

    function initYTShortsSwipers() {
        const containers = document.querySelectorAll('.ytss-swiper');
        
        containers.forEach(function(container) {
            initSwiper(container);
            initVideoPlayers(container);
        });
    }

    function initSwiper(container) {
        const settings = window.ytss_settings || {};
        
        const swiperConfig = {
            slidesPerView: settings.slides_mobile || 2,
            spaceBetween: 15,
            grabCursor: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: settings.slides_tablet || 3,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: settings.slides_desktop || 4,
                    spaceBetween: 20
                }
            }
        };

        // Autoplay ayarı
        const autoplaySpeed = parseInt(settings.autoplay_speed);
        if (autoplaySpeed > 0) {
            swiperConfig.autoplay = {
                delay: autoplaySpeed,
                disableOnInteraction: true,
                pauseOnMouseEnter: true
            };
        }

        // Swiper'ı başlat
        const swiper = new Swiper(container, swiperConfig);

        // Video oynatıldığında autoplay'i durdur
        container.addEventListener('ytss-video-play', function() {
            if (swiper.autoplay && swiper.autoplay.running) {
                swiper.autoplay.stop();
            }
        });

        // Video kapatıldığında autoplay'i devam ettir
        container.addEventListener('ytss-video-stop', function() {
            if (swiper.autoplay && autoplaySpeed > 0) {
                swiper.autoplay.start();
            }
        });
    }

    function initVideoPlayers(container) {
        const slides = container.querySelectorAll('.ytss-slide');

        slides.forEach(function(slide) {
            const thumbnail = slide.querySelector('.ytss-thumbnail');
            const embedContainer = slide.querySelector('.ytss-video-embed');
            const videoId = slide.dataset.videoId;
            const videoUrl = slide.dataset.videoUrl;

            if (!thumbnail || !embedContainer || !videoId) return;

            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Diğer oynatılan videoları durdur
                stopAllVideos(container);

                // Bu videoyu oynat
                playVideo(slide, videoId, videoUrl, embedContainer);

                // Custom event tetikle
                container.dispatchEvent(new CustomEvent('ytss-video-play'));
            });
        });
    }

    function playVideo(slide, videoId, videoUrl, embedContainer) {
        // YouTube Shorts için özel embed URL
        // Shorts videolarında /shorts/ URL'si embed'de çalışmıyor, standart embed kullanıyoruz
        const embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1&playsinline=1&mute=0&enablejsapi=1`;
        
        // iframe oluştur
        const iframe = document.createElement('iframe');
        iframe.src = embedUrl;
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        // Embed container'a ekle
        embedContainer.innerHTML = '';
        embedContainer.appendChild(iframe);
        embedContainer.style.display = 'block';

        // Playing class ekle
        slide.classList.add('ytss-playing');
    }

    function stopAllVideos(container) {
        const playingSlides = container.querySelectorAll('.ytss-slide.ytss-playing');
        
        playingSlides.forEach(function(slide) {
            const embedContainer = slide.querySelector('.ytss-video-embed');
            const thumbnail = slide.querySelector('.ytss-thumbnail');
            
            // iframe'i kaldır
            embedContainer.innerHTML = '';
            embedContainer.style.display = 'none';
            
            // Thumbnail'ı tekrar göster
            if (thumbnail) {
                thumbnail.style.display = 'block';
            }
            
            slide.classList.remove('ytss-playing');
        });

        // Custom event tetikle
        container.dispatchEvent(new CustomEvent('ytss-video-stop'));
    }

    // Sayfa görünürlüğü değiştiğinde videoları durdur
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            document.querySelectorAll('.ytss-swiper').forEach(function(container) {
                stopAllVideos(container);
            });
        }
    });

    // ESC tuşu ile videoyu kapat
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.ytss-swiper').forEach(function(container) {
                stopAllVideos(container);
            });
        }
    });

    // Global erişim için
    window.YTShortsSwiper = {
        init: initYTShortsSwipers,
        stopAll: function() {
            document.querySelectorAll('.ytss-swiper').forEach(stopAllVideos);
        }
    };

})();
