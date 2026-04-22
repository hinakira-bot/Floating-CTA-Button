<?php
/**
 * Plugin Name: Floating CTA Button
 * Plugin URI:  https://hinakira.com/
 * Description: 画面下部に追従するフローティングCTAボタン。テキスト・リンク・デザイン・マイクロコピーを管理画面から設定できます。
 * Version:     1.0.0
 * Author:      ヒナキラ
 * Author URI:  https://hinakira.com/
 * License:     GPL-2.0-or-later
 * Text Domain: wp-floating-cta
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_FLOATING_CTA_VERSION', '1.0.0' );
define( 'WP_FLOATING_CTA_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_FLOATING_CTA_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_FLOATING_CTA_OPTION', 'wp_floating_cta_settings' );

// 管理画面
require_once WP_FLOATING_CTA_DIR . 'admin/settings.php';

/**
 * 管理画面: 設定ページにのみ CSS・JS を読み込む
 */
add_action( 'admin_enqueue_scripts', 'wp_floating_cta_admin_enqueue' );
function wp_floating_cta_admin_enqueue( string $hook ): void {
    if ( $hook !== 'settings_page_wp-floating-cta' ) {
        return;
    }
    wp_enqueue_style(
        'wp-floating-cta',
        WP_FLOATING_CTA_URL . 'assets/css/floating-cta.css',
        [],
        WP_FLOATING_CTA_VERSION
    );
    wp_enqueue_script(
        'wp-floating-cta-admin',
        WP_FLOATING_CTA_URL . 'assets/js/admin-preview.js',
        [],
        WP_FLOATING_CTA_VERSION,
        true
    );
}

/**
 * デフォルト設定値
 */
function wp_floating_cta_defaults(): array {
    return [
        'enabled'              => '1',
        'button_text'          => '今すぐ申し込む',
        'button_url'           => '',
        'button_target'        => '_blank',
        'micro_copy_top'       => '✨ 期間限定キャンペーン中',
        'micro_copy_bottom'    => '⏰ お申し込みはお早めに',
        'bg_color'             => '#ff6b35',
        'text_color'           => '#ffffff',
        'border_radius'        => '8',
        'font_size'            => '18',
        'padding_v'            => '16',
        'padding_h'            => '32',
        'position'             => 'center',
        'bottom_offset'        => '20',
        'show_close'           => '1',
        'max_width'            => '480',
        'shadow'               => '1',
        'animation'            => 'slide',
        'button_animation'     => 'shine',
        'bg_padding_v'         => '12',
        'bg_padding_h'         => '16',
        'full_width'           => '',
        'micro_top_color'      => '#333333',
        'micro_top_size'       => '13',
        'micro_bottom_color'   => '#666666',
        'micro_bottom_size'    => '12',
    ];
}

/**
 * 設定値を取得（デフォルトとマージ）
 */
function wp_floating_cta_get_settings(): array {
    $saved    = get_option( WP_FLOATING_CTA_OPTION, [] );
    $defaults = wp_floating_cta_defaults();
    return wp_parse_args( $saved, $defaults );
}

/**
 * フロントエンド: CSS・JS・HTML を出力
 */
add_action( 'wp_enqueue_scripts', 'wp_floating_cta_enqueue' );
function wp_floating_cta_enqueue(): void {
    $settings = wp_floating_cta_get_settings();
    if ( empty( $settings['enabled'] ) ) {
        return;
    }
    wp_enqueue_style(
        'wp-floating-cta',
        WP_FLOATING_CTA_URL . 'assets/css/floating-cta.css',
        [],
        WP_FLOATING_CTA_VERSION
    );
    wp_enqueue_script(
        'wp-floating-cta',
        WP_FLOATING_CTA_URL . 'assets/js/floating-cta.js',
        [],
        WP_FLOATING_CTA_VERSION,
        true
    );
}

add_action( 'wp_footer', 'wp_floating_cta_render' );
function wp_floating_cta_render(): void {
    $s = wp_floating_cta_get_settings();
    if ( empty( $s['enabled'] ) || empty( $s['button_url'] ) ) {
        return;
    }

    // 動的スタイルをインライン出力
    $btn_style = sprintf(
        'background-color:%s;color:%s;border-radius:%spx;font-size:%spx;padding:%spx %spx;',
        esc_attr( $s['bg_color'] ),
        esc_attr( $s['text_color'] ),
        (int) $s['border_radius'],
        (int) $s['font_size'],
        (int) $s['padding_v'],
        (int) $s['padding_h']
    );

    $position_style = '';
    switch ( $s['position'] ) {
        case 'left':
            $position_style = 'left:20px;right:auto;transform:none;';
            break;
        case 'right':
            $position_style = 'right:20px;left:auto;transform:none;';
            break;
        default: // center
            $position_style = 'left:50%;transform:translateX(-50%);';
    }

    $wrap_style = sprintf(
        'bottom:%spx;max-width:%spx;%s',
        (int) $s['bottom_offset'],
        (int) $s['max_width'],
        $position_style
    );

    $shadow_class    = ! empty( $s['shadow'] ) ? ' fcta-shadow' : '';
    $animation_class = 'fcta-anim-' . esc_attr( $s['animation'] );
    $target          = in_array( $s['button_target'], [ '_blank', '_self' ], true ) ? $s['button_target'] : '_blank';
    $rel             = ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : '';

    $valid_btn_anims = [ 'float', 'shine', 'pulse', 'none' ];
    $btn_anim        = in_array( $s['button_animation'] ?? 'none', $valid_btn_anims, true )
                           ? $s['button_animation'] : 'none';
    $btn_anim_class  = ( $btn_anim !== 'none' ) ? ' fcta-btn-' . esc_attr( $btn_anim ) : '';

    $full_width      = ! empty( $s['full_width'] );
    $fullwidth_class = $full_width ? ' fcta-fullwidth' : '';

    // フルワイドのときは位置・幅をすべて上書き
    if ( $full_width ) {
        $wrap_style = 'bottom:0;left:0;right:0;transform:none;max-width:100%;';
    }

    $wrap_style .= sprintf(
        'padding:%spx %spx;',
        (int) $s['bg_padding_v'],
        (int) $s['bg_padding_h']
    );

    ?>
    <div id="wp-floating-cta" class="wp-floating-cta <?php echo esc_attr( $animation_class . $shadow_class . $fullwidth_class ); ?>"
         style="<?php echo esc_attr( $wrap_style ); ?>"
         role="complementary" aria-label="CTAボタン">

        <?php if ( ! empty( $s['show_close'] ) ) : ?>
        <button class="fcta-close" aria-label="閉じる" title="閉じる">&#x2715;</button>
        <?php endif; ?>

        <?php if ( ! empty( trim( $s['micro_copy_top'] ) ) ) : ?>
        <p class="fcta-micro fcta-micro-top" style="color:<?php echo esc_attr( $s['micro_top_color'] ); ?>;font-size:<?php echo (int) $s['micro_top_size']; ?>px;">
            <?php echo wp_kses_post( $s['micro_copy_top'] ); ?>
        </p>
        <?php endif; ?>

        <a href="<?php echo esc_url( $s['button_url'] ); ?>"
           target="<?php echo esc_attr( $target ); ?>"<?php echo $rel; ?>
           class="fcta-btn<?php echo esc_attr( $btn_anim_class ); ?>"
           style="<?php echo esc_attr( $btn_style ); ?>">
            <?php echo wp_kses_post( $s['button_text'] ); ?>
        </a>

        <?php if ( ! empty( trim( $s['micro_copy_bottom'] ) ) ) : ?>
        <p class="fcta-micro fcta-micro-bottom" style="color:<?php echo esc_attr( $s['micro_bottom_color'] ); ?>;font-size:<?php echo (int) $s['micro_bottom_size']; ?>px;">
            <?php echo wp_kses_post( $s['micro_copy_bottom'] ); ?>
        </p>
        <?php endif; ?>
    </div>
    <?php
}
