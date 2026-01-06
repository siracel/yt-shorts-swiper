<?php
if (!defined('ABSPATH')) {
    exit;
}

$videos = get_option('ytss_videos', []);
$settings = get_option('ytss_settings', []);
?>

<div class="wrap ytss-admin-wrap">
    <h1>
        <span class="dashicons dashicons-video-alt3"></span>
        YouTube Shorts Swiper
    </h1>
    
    <div class="ytss-admin-container">
        <!-- Sol Panel: Video Listesi -->
        <div class="ytss-panel ytss-videos-panel">
            <div class="ytss-panel-header">
                <h2>📹 Video Listesi</h2>
                <button type="button" id="ytss-add-video" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Yeni Video Ekle
                </button>
            </div>
            
            <div class="ytss-panel-body">
                <div id="ytss-video-list">
                    <?php if (empty($videos)): ?>
                        <div class="ytss-empty-state">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <p>Henüz video eklenmemiş</p>
                            <p class="description">Yukarıdaki "Yeni Video Ekle" butonuna tıklayarak başlayın</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videos as $index => $video): ?>
                            <div class="ytss-video-item" data-index="<?php echo $index; ?>">
                                <div class="ytss-video-drag">
                                    <span class="dashicons dashicons-menu"></span>
                                </div>
                                <div class="ytss-video-thumb">
                                    <?php 
                                    $video_id = ytss_get_video_id($video['url']);
                                    if ($video_id):
                                    ?>
                                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/default.jpg" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="ytss-video-fields">
                                    <input type="url" class="ytss-input ytss-url" placeholder="YouTube Shorts URL" value="<?php echo esc_url($video['url']); ?>">
                                    <input type="text" class="ytss-input ytss-title" placeholder="Video Başlığı (opsiyonel)" value="<?php echo esc_attr($video['title']); ?>">
                                    <input type="text" class="ytss-input ytss-channel" placeholder="Kanal Adı (opsiyonel)" value="<?php echo esc_attr($video['channel']); ?>">
                                </div>
                                <button type="button" class="ytss-remove-video" title="Videoyu Sil">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="ytss-save-section">
                    <button type="button" id="ytss-save-videos" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span> Videoları Kaydet
                    </button>
                    <span id="ytss-save-status"></span>
                </div>
            </div>
        </div>
        
        <!-- Sağ Panel: Ayarlar -->
        <div class="ytss-panel ytss-settings-panel">
            <div class="ytss-panel-header">
                <h2>⚙️ Ayarlar</h2>
            </div>
            
            <div class="ytss-panel-body">
                <div class="ytss-setting-group">
                    <label>Autoplay Hızı (ms)</label>
                    <select id="ytss-autoplay-speed">
                        <option value="2000" <?php selected($settings['autoplay_speed'], 2000); ?>>2 saniye</option>
                        <option value="3000" <?php selected($settings['autoplay_speed'], 3000); ?>>3 saniye</option>
                        <option value="4000" <?php selected($settings['autoplay_speed'], 4000); ?>>4 saniye</option>
                        <option value="5000" <?php selected($settings['autoplay_speed'], 5000); ?>>5 saniye</option>
                        <option value="0" <?php selected($settings['autoplay_speed'], 0); ?>>Kapalı</option>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label>
                        <input type="checkbox" id="ytss-show-title" <?php checked($settings['show_title'], true); ?>>
                        Başlık Göster
                    </label>
                </div>
                
                <div class="ytss-setting-group">
                    <label>
                        <input type="checkbox" id="ytss-show-channel" <?php checked($settings['show_channel'], true); ?>>
                        Kanal Adı Göster
                    </label>
                </div>
                
                <div class="ytss-setting-group">
                    <label>
                        <input type="checkbox" id="ytss-show-navigation" <?php checked($settings['show_navigation'], true); ?>>
                        Navigation Okları Göster
                    </label>
                </div>
                
                <hr>
                
                <h3>📱 Responsive Ayarları</h3>
                
                <div class="ytss-setting-group">
                    <label>Desktop (1024px+)</label>
                    <select id="ytss-slides-desktop">
                        <?php for ($i = 2; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_desktop'], $i); ?>><?php echo $i; ?> video</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label>Tablet (768px - 1023px)</label>
                    <select id="ytss-slides-tablet">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_tablet'], $i); ?>><?php echo $i; ?> video</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label>Mobil (767px ve altı)</label>
                    <select id="ytss-slides-mobile">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_mobile'], $i); ?>><?php echo $i; ?> video</option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-save-section">
                    <button type="button" id="ytss-save-settings" class="button button-primary">
                        <span class="dashicons dashicons-admin-generic"></span> Ayarları Kaydet
                    </button>
                    <span id="ytss-settings-status"></span>
                </div>
            </div>
            
            <!-- Shortcode Bilgisi -->
            <div class="ytss-panel-body ytss-shortcode-box">
                <h3>📝 Shortcode</h3>
                <div class="ytss-shortcode-display">
                    <code id="ytss-shortcode">[yt_shorts_swiper]</code>
                    <button type="button" id="ytss-copy-shortcode" class="button" title="Kopyala">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                </div>
                <p class="description">Bu shortcode'u herhangi bir sayfaya veya yazıya ekleyerek slider'ı görüntüleyebilirsiniz.</p>
            </div>
        </div>
    </div>
</div>

<!-- Video Item Template -->
<template id="ytss-video-template">
    <div class="ytss-video-item">
        <div class="ytss-video-drag">
            <span class="dashicons dashicons-menu"></span>
        </div>
        <div class="ytss-video-thumb">
            <span class="dashicons dashicons-format-video"></span>
        </div>
        <div class="ytss-video-fields">
            <input type="url" class="ytss-input ytss-url" placeholder="YouTube Shorts URL">
            <input type="text" class="ytss-input ytss-title" placeholder="Video Başlığı (opsiyonel)">
            <input type="text" class="ytss-input ytss-channel" placeholder="Kanal Adı (opsiyonel)">
        </div>
        <button type="button" class="ytss-remove-video" title="Videoyu Sil">
            <span class="dashicons dashicons-trash"></span>
        </button>
    </div>
</template>
