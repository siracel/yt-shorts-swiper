<?php
/**
 * Plugin Name: YouTube Shorts Swiper
 * Plugin URI: https://tebilisim.com
 * Description: YouTube Shorts videolarını swiper slider olarak gösteren eklenti
 * Version: 1.1.0
 * Author: TE Bilişim
 * Author URI: https://tebilisim.com
 * License: GPL v2 or later
 * Text Domain: yt-shorts-swiper
 */

// Direkt erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

// Sabitler
define('YTSS_VERSION', '1.1.0');
define('YTSS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YTSS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Aktivasyon hook - varsayılan ayarları oluştur
 */
register_activation_hook(__FILE__, function() {
    add_option('ytss_videos', []);
    add_option('ytss_settings', [
        'autoplay_speed' => 3000,
        'show_title' => true,
        'show_channel' => true,
        'show_navigation' => true,
        'slides_desktop' => 4,
        'slides_tablet' => 3,
        'slides_mobile' => 2,
    ]);
});

/**
 * Admin menü oluştur
 */
add_action('admin_menu', function() {
    add_menu_page(
        'YouTube Shorts Swiper',
        'YT Shorts Swiper',
        'manage_options',
        'yt-shorts-swiper',
        'ytss_admin_page',
        'dashicons-video-alt3',
        30
    );
});

/**
 * Admin sayfası
 */
function ytss_admin_page() {
    include YTSS_PLUGIN_DIR . 'admin/admin-page.php';
}

/**
 * Admin scriptleri ve stilleri
 */
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_yt-shorts-swiper') {
        return;
    }
    
    wp_enqueue_style('ytss-admin-style', YTSS_PLUGIN_URL . 'admin/admin-style.css', [], YTSS_VERSION);
    wp_enqueue_script('ytss-admin-script', YTSS_PLUGIN_URL . 'admin/admin-script.js', ['jquery'], YTSS_VERSION, true);
    
    wp_localize_script('ytss-admin-script', 'ytss_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ytss_nonce')
    ]);
});

/**
 * Frontend scriptleri ve stilleri
 */
add_action('wp_enqueue_scripts', function() {
    // Swiper CSS & JS (CDN)
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);
    
    // Plugin CSS & JS
    wp_enqueue_style('ytss-style', YTSS_PLUGIN_URL . 'public/swiper-style.css', ['swiper-css'], YTSS_VERSION);
    wp_enqueue_script('ytss-script', YTSS_PLUGIN_URL . 'public/swiper-init.js', ['swiper-js'], YTSS_VERSION, true);
    
    // Ayarları JS'e aktar
    $settings = get_option('ytss_settings', []);
    wp_localize_script('ytss-script', 'ytss_settings', $settings);
});

/**
 * AJAX: Videoları kaydet
 */
add_action('wp_ajax_ytss_save_videos', function() {
    check_ajax_referer('ytss_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Yetkiniz yok');
    }
    
    $videos = isset($_POST['videos']) ? $_POST['videos'] : [];
    $sanitized_videos = [];
    
    foreach ($videos as $video) {
        $sanitized_videos[] = [
            'url' => esc_url_raw($video['url']),
            'title' => sanitize_text_field($video['title']),
            'channel' => sanitize_text_field($video['channel']),
        ];
    }
    
    update_option('ytss_videos', $sanitized_videos);
    wp_send_json_success('Kaydedildi');
});

/**
 * AJAX: Ayarları kaydet
 */
add_action('wp_ajax_ytss_save_settings', function() {
    check_ajax_referer('ytss_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Yetkiniz yok');
    }
    
    $settings = [
        'autoplay_speed' => intval($_POST['autoplay_speed']),
        'show_title' => isset($_POST['show_title']) && $_POST['show_title'] === 'true',
        'show_channel' => isset($_POST['show_channel']) && $_POST['show_channel'] === 'true',
        'show_navigation' => isset($_POST['show_navigation']) && $_POST['show_navigation'] === 'true',
        'slides_desktop' => intval($_POST['slides_desktop']),
        'slides_tablet' => intval($_POST['slides_tablet']),
        'slides_mobile' => intval($_POST['slides_mobile']),
    ];
    
    update_option('ytss_settings', $settings);
    wp_send_json_success('Ayarlar kaydedildi');
});

/**
 * YouTube URL'den video ID çıkar
 */
function ytss_get_video_id($url) {
    $patterns = [
        '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/',
        '/youtu\.be\/([a-zA-Z0-9_-]+)/',
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return false;
}

/**
 * Shortcode: [yt_shorts_swiper]
 */
add_shortcode('yt_shorts_swiper', function($atts) {
    $videos = get_option('ytss_videos', []);
    $settings = get_option('ytss_settings', []);
    
    if (empty($videos)) {
        return '<p class="ytss-no-videos">Henüz video eklenmemiş.</p>';
    }
    
    ob_start();
    ?>
    <div class="ytss-container">
        <div class="swiper ytss-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($videos as $video): 
                    $video_id = ytss_get_video_id($video['url']);
                    if (!$video_id) continue;
                    $thumbnail_hq = "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg";
                    $thumbnail_fallback = "https://img.youtube.com/vi/{$video_id}/0.jpg";
                ?>
                <div class="swiper-slide ytss-slide" data-video-id="<?php echo esc_attr($video_id); ?>" data-video-url="<?php echo esc_url($video['url']); ?>">
                    <div class="ytss-video-wrapper">
                        <div class="ytss-thumbnail">
                            <img src="<?php echo esc_url($thumbnail_hq); ?>" alt="<?php echo esc_attr($video['title']); ?>" onerror="this.src='<?php echo esc_url($thumbnail_fallback); ?>'">
                            <div class="ytss-play-button">
                                <svg viewBox="0 0 68 48" width="68" height="48">
                                    <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"/>
                                    <path d="M 45,24 27,14 27,34" fill="#fff"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ytss-video-embed" style="display: none;"></div>
                    </div>
                    <?php if (!empty($settings['show_title']) || !empty($settings['show_channel'])): ?>
                    <div class="ytss-info">
                        <?php if (!empty($settings['show_title']) && !empty($video['title'])): ?>
                            <div class="ytss-title"><?php echo esc_html($video['title']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($settings['show_channel']) && !empty($video['channel'])): ?>
                            <div class="ytss-channel"><?php echo esc_html($video['channel']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($settings['show_navigation'])): ?>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
