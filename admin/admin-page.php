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
        <?php esc_html_e('YouTube Shorts Swiper', 'yt-shorts-swiper'); ?>
    </h1>
    
    <div class="ytss-admin-container">
        <!-- Sol Panel: Video Listesi -->
        <div class="ytss-panel ytss-videos-panel">
            <div class="ytss-panel-header">
                <h2>📹 <?php esc_html_e('Video List', 'yt-shorts-swiper'); ?></h2>
                <button type="button" id="ytss-add-video" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add New Video', 'yt-shorts-swiper'); ?>
                </button>
            </div>
            
            <div class="ytss-panel-body">
                <div id="ytss-video-list">
                    <?php if (empty($videos)): ?>
                        <div class="ytss-empty-state">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <p><?php esc_html_e('No videos added yet', 'yt-shorts-swiper'); ?></p>
                            <p class="description"><?php esc_html_e('Click "Add New Video" button above to get started', 'yt-shorts-swiper'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videos as $index => $video_url): 
                            // Eski format desteği
                            if (is_array($video_url)) {
                                $video_url = $video_url['url'];
                            }
                            $video_id = ytss_get_video_id($video_url);
                        ?>
                            <div class="ytss-video-item" data-index="<?php echo $index; ?>">
                                <div class="ytss-video-drag">
                                    <span class="dashicons dashicons-menu"></span>
                                </div>
                                <div class="ytss-video-thumb">
                                    <?php if ($video_id): ?>
                                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/default.jpg" alt="">
                                    <?php else: ?>
                                        <span class="dashicons dashicons-format-video"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ytss-video-fields">
                                    <input type="url" class="ytss-input ytss-url" placeholder="<?php esc_attr_e('YouTube Shorts URL', 'yt-shorts-swiper'); ?>" value="<?php echo esc_url($video_url); ?>">
                                </div>
                                <button type="button" class="ytss-remove-video" title="<?php esc_attr_e('Delete Video', 'yt-shorts-swiper'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="ytss-save-section">
                    <button type="button" id="ytss-save-videos" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span> <?php esc_html_e('Save Videos', 'yt-shorts-swiper'); ?>
                    </button>
                    <span id="ytss-save-status"></span>
                </div>
            </div>
        </div>
        
        <!-- Sağ Panel: Ayarlar -->
        <div class="ytss-panel ytss-settings-panel">
            <div class="ytss-panel-header">
                <h2>⚙️ <?php esc_html_e('Settings', 'yt-shorts-swiper'); ?></h2>
            </div>
            
            <div class="ytss-panel-body">
                <div class="ytss-setting-group">
                    <label><?php esc_html_e('Autoplay Speed (ms)', 'yt-shorts-swiper'); ?></label>
                    <select id="ytss-autoplay-speed">
                        <option value="2000" <?php selected($settings['autoplay_speed'] ?? 3000, 2000); ?>><?php esc_html_e('2 seconds', 'yt-shorts-swiper'); ?></option>
                        <option value="3000" <?php selected($settings['autoplay_speed'] ?? 3000, 3000); ?>><?php esc_html_e('3 seconds', 'yt-shorts-swiper'); ?></option>
                        <option value="4000" <?php selected($settings['autoplay_speed'] ?? 3000, 4000); ?>><?php esc_html_e('4 seconds', 'yt-shorts-swiper'); ?></option>
                        <option value="5000" <?php selected($settings['autoplay_speed'] ?? 3000, 5000); ?>><?php esc_html_e('5 seconds', 'yt-shorts-swiper'); ?></option>
                        <option value="0" <?php selected($settings['autoplay_speed'] ?? 3000, 0); ?>><?php esc_html_e('Disabled', 'yt-shorts-swiper'); ?></option>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label>
                        <input type="checkbox" id="ytss-show-navigation" <?php checked($settings['show_navigation'] ?? true, true); ?>>
                        <?php esc_html_e('Show Navigation Arrows', 'yt-shorts-swiper'); ?>
                    </label>
                </div>
                
                <hr>
                
                <h3>📱 <?php esc_html_e('Responsive Settings', 'yt-shorts-swiper'); ?></h3>
                
                <div class="ytss-setting-group">
                    <label><?php esc_html_e('Desktop (1024px+)', 'yt-shorts-swiper'); ?></label>
                    <select id="ytss-slides-desktop">
                        <?php for ($i = 2; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_desktop'] ?? 4, $i); ?>>
                                <?php printf(esc_html(_n('%d video', '%d videos', $i, 'yt-shorts-swiper')), $i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label><?php esc_html_e('Tablet (768px - 1023px)', 'yt-shorts-swiper'); ?></label>
                    <select id="ytss-slides-tablet">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_tablet'] ?? 3, $i); ?>>
                                <?php printf(esc_html(_n('%d video', '%d videos', $i, 'yt-shorts-swiper')), $i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-setting-group">
                    <label><?php esc_html_e('Mobile (767px and below)', 'yt-shorts-swiper'); ?></label>
                    <select id="ytss-slides-mobile">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($settings['slides_mobile'] ?? 2, $i); ?>>
                                <?php printf(esc_html(_n('%d video', '%d videos', $i, 'yt-shorts-swiper')), $i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="ytss-save-section">
                    <button type="button" id="ytss-save-settings" class="button button-primary">
                        <span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e('Save Settings', 'yt-shorts-swiper'); ?>
                    </button>
                    <span id="ytss-settings-status"></span>
                </div>
            </div>
            
            <!-- Shortcode Bilgisi -->
            <div class="ytss-panel-body ytss-shortcode-box">
                <h3>📝 <?php esc_html_e('Shortcode', 'yt-shorts-swiper'); ?></h3>
                <div class="ytss-shortcode-display">
                    <code id="ytss-shortcode">[yt_shorts_swiper]</code>
                    <button type="button" id="ytss-copy-shortcode" class="button" title="<?php esc_attr_e('Copy', 'yt-shorts-swiper'); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                </div>
                <p class="description"><?php esc_html_e('Add this shortcode to any page or post to display the slider.', 'yt-shorts-swiper'); ?></p>
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
            <input type="url" class="ytss-input ytss-url" placeholder="<?php esc_attr_e('YouTube Shorts URL', 'yt-shorts-swiper'); ?>">
        </div>
        <button type="button" class="ytss-remove-video" title="<?php esc_attr_e('Delete Video', 'yt-shorts-swiper'); ?>">
            <span class="dashicons dashicons-trash"></span>
        </button>
    </div>
</template>
