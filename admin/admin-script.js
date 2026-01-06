/**
 * YouTube Shorts Swiper - Admin Script
 */
(function($) {
    'use strict';

    // YouTube URL'den video ID çıkar
    function getVideoId(url) {
        const patterns = [
            /youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/,
            /youtu\.be\/([a-zA-Z0-9_-]+)/,
            /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/
        ];

        for (let pattern of patterns) {
            const match = url.match(pattern);
            if (match) {
                return match[1];
            }
        }
        return null;
    }

    // Thumbnail güncelle
    function updateThumbnail($item) {
        const url = $item.find('.ytss-url').val();
        const videoId = getVideoId(url);
        const $thumb = $item.find('.ytss-video-thumb');

        if (videoId) {
            $thumb.html(`<img src="https://img.youtube.com/vi/${videoId}/default.jpg" alt="">`);
        } else {
            $thumb.html('<span class="dashicons dashicons-format-video"></span>');
        }
    }

    // Yeni video ekle
    $('#ytss-add-video').on('click', function() {
        const template = document.getElementById('ytss-video-template');
        const $newItem = $(template.content.cloneNode(true));
        
        // Empty state varsa kaldır
        $('.ytss-empty-state').remove();
        
        $('#ytss-video-list').append($newItem);
        
        // Yeni eklenen item'a focus
        $('#ytss-video-list .ytss-video-item:last-child .ytss-url').focus();
    });

    // Video sil
    $(document).on('click', '.ytss-remove-video', function() {
        const $item = $(this).closest('.ytss-video-item');
        
        if (confirm('Bu videoyu silmek istediğinize emin misiniz?')) {
            $item.fadeOut(300, function() {
                $(this).remove();
                
                // Liste boşaldıysa empty state göster
                if ($('#ytss-video-list .ytss-video-item').length === 0) {
                    $('#ytss-video-list').html(`
                        <div class="ytss-empty-state">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <p>Henüz video eklenmemiş</p>
                            <p class="description">Yukarıdaki "Yeni Video Ekle" butonuna tıklayarak başlayın</p>
                        </div>
                    `);
                }
            });
        }
    });

    // URL değiştiğinde thumbnail güncelle
    $(document).on('blur', '.ytss-url', function() {
        updateThumbnail($(this).closest('.ytss-video-item'));
    });

    // Videoları kaydet
    $('#ytss-save-videos').on('click', function() {
        const $button = $(this);
        const $status = $('#ytss-save-status');
        const videos = [];

        $('#ytss-video-list .ytss-video-item').each(function() {
            const $item = $(this);
            const url = $item.find('.ytss-url').val().trim();
            
            if (url) {
                videos.push({
                    url: url,
                    title: $item.find('.ytss-title').val().trim(),
                    channel: $item.find('.ytss-channel').val().trim()
                });
            }
        });

        $button.prop('disabled', true).text('Kaydediliyor...');
        $status.text('');

        $.ajax({
            url: ytss_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ytss_save_videos',
                nonce: ytss_ajax.nonce,
                videos: videos
            },
            success: function(response) {
                if (response.success) {
                    $status.css('color', '#00a32a').text('✓ Kaydedildi!');
                } else {
                    $status.css('color', '#d63638').text('✗ Hata: ' + response.data);
                }
            },
            error: function() {
                $status.css('color', '#d63638').text('✗ Bağlantı hatası');
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Videoları Kaydet');
                
                setTimeout(function() {
                    $status.fadeOut(300, function() {
                        $(this).text('').show();
                    });
                }, 3000);
            }
        });
    });

    // Ayarları kaydet
    $('#ytss-save-settings').on('click', function() {
        const $button = $(this);
        const $status = $('#ytss-settings-status');

        const settings = {
            action: 'ytss_save_settings',
            nonce: ytss_ajax.nonce,
            autoplay_speed: $('#ytss-autoplay-speed').val(),
            show_title: $('#ytss-show-title').is(':checked').toString(),
            show_channel: $('#ytss-show-channel').is(':checked').toString(),
            show_navigation: $('#ytss-show-navigation').is(':checked').toString(),
            slides_desktop: $('#ytss-slides-desktop').val(),
            slides_tablet: $('#ytss-slides-tablet').val(),
            slides_mobile: $('#ytss-slides-mobile').val()
        };

        $button.prop('disabled', true).text('Kaydediliyor...');
        $status.text('');

        $.ajax({
            url: ytss_ajax.ajax_url,
            type: 'POST',
            data: settings,
            success: function(response) {
                if (response.success) {
                    $status.css('color', '#00a32a').text('✓ Kaydedildi!');
                } else {
                    $status.css('color', '#d63638').text('✗ Hata: ' + response.data);
                }
            },
            error: function() {
                $status.css('color', '#d63638').text('✗ Bağlantı hatası');
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Ayarları Kaydet');
                
                setTimeout(function() {
                    $status.fadeOut(300, function() {
                        $(this).text('').show();
                    });
                }, 3000);
            }
        });
    });

    // Shortcode kopyala
    $('#ytss-copy-shortcode').on('click', function() {
        const shortcode = $('#ytss-shortcode').text();
        
        navigator.clipboard.writeText(shortcode).then(function() {
            const $btn = $('#ytss-copy-shortcode');
            const originalHtml = $btn.html();
            
            $btn.html('<span class="dashicons dashicons-yes"></span>');
            
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 2000);
        });
    });

    // Enter tuşu ile kaydet
    $(document).on('keypress', '.ytss-input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#ytss-save-videos').click();
        }
    });

})(jQuery);
