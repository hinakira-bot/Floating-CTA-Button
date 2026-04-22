<?php
/**
 * Plugin Name: Floating CTA Button
 * Plugin URI:  https://hinakira.com/
 * Description: 画面下部に追従するフローティングCTAボタン。テキスト・リンク・デザイン・マイクロコピーを管理画面から設定できます。
 * Version:     1.1.0
 * Author:      ヒナキラ
 * Author URI:  https://hinakira.com/
 * License:     GPL-2.0-or-later
 * Text Domain: wp-floating-cta
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_FLOATING_CTA_VERSION', '1.1.0' );
define( 'WP_FLOATING_CTA_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_FLOATING_CTA_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_FLOATING_CTA_OPTION', 'wp_floating_cta_settings' );

require_once WP_FLOATING_CTA_DIR . 'admin/settings.php';

/* ------------------------------------------------------------------
   管理画面: 設定ページにのみ CSS・JS を読み込む
------------------------------------------------------------------ */
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

/* ------------------------------------------------------------------
   デフォルト設定値
------------------------------------------------------------------ */
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
        'bg_padding_v'         => '12',
        'bg_padding_h'         => '16',
        'position'             => 'center',
        'bottom_offset'        => '20',
        'max_width'            => '480',
        'show_close'           => '1',
        'shadow'               => '1',
        'full_width'           => '',
        'button_3d'            => '1',
        'animation'            => 'slide',
        'button_animation'     => 'shine',
        'mobile_font_size'     => '16',
        'mobile_padding_v'     => '14',
        'mobile_padding_h'     => '20',
        'mobile_bg_padding_v'  => '10',
        'mobile_bg_padding_h'  => '12',
        'micro_top_color'      => '#333333',
        'micro_top_size'       => '13',
        'micro_bottom_color'   => '#666666',
        'micro_bottom_size'    => '12',
    ];
}

/* ------------------------------------------------------------------
   設定値を取得（デフォルトとマージ）
------------------------------------------------------------------ */
function wp_floating_cta_get_settings(): array {
    $saved    = (array) get_option( WP_FLOATING_CTA_OPTION, [] );
    $defaults = wp_floating_cta_defaults();
    return wp_parse_args( $saved, $defaults );
}

/* ------------------------------------------------------------------
   フロントエンド: CSS・JS のエンキュー
------------------------------------------------------------------ */
add_action( 'wp_enqueue_scripts', 'wp_floating_cta_enqueue' );
function wp_floating_cta_enqueue(): void {
    $s = wp_floating_cta_get_settings();
    if ( empty( $s['enabled'] ) ) {
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

    // モバイル上書き CSS
    $mobile_css = wp_floating_cta_mobile_css( $s );
    if ( $mobile_css ) {
        wp_add_inline_style( 'wp-floating-cta', $mobile_css );
    }
}

/* ------------------------------------------------------------------
   モバイル用インライン CSS 生成
------------------------------------------------------------------ */
function wp_floating_cta_mobile_css( array $s ): string {
    $btn_rules  = [];
    $wrap_rules = [];

    $mfs  = absint( $s['mobile_font_size']    ?? 0 );
    $mpv  = absint( $s['mobile_padding_v']    ?? 0 );
    $mph  = absint( $s['mobile_padding_h']    ?? 0 );
    $mbpv = absint( $s['mobile_bg_padding_v'] ?? 0 );
    $mbph = absint( $s['mobile_bg_padding_h'] ?? 0 );

    if ( $mfs > 0 ) {
        $btn_rules[] = "font-size:{$mfs}px !important";
    }
    if ( $mpv > 0 || $mph > 0 ) {
        $btn_rules[] = "padding:{$mpv}px {$mph}px !important";
    }
    if ( $mbpv > 0 || $mbph > 0 ) {
        $wrap_rules[] = "padding:{$mbpv}px {$mbph}px !important";
    }

    $css = '';
    if ( $btn_rules ) {
        $css .= '@media(max-width:767px){#wp-floating-cta .fcta-btn{' . implode( ';', $btn_rules ) . '}}';
    }
    if ( $wrap_rules ) {
        $css .= '@media(max-width:767px){#wp-floating-cta{' . implode( ';', $wrap_rules ) . '}}';
    }
    return $css;
}

/* ------------------------------------------------------------------
   フロントエンド: HTML 出力
------------------------------------------------------------------ */
add_action( 'wp_footer', 'wp_floating_cta_render' );
function wp_floating_cta_render(): void {
    $s = wp_floating_cta_get_settings();

    // 表示条件チェック
    if ( empty( $s['enabled'] ) || empty( $s['button_url'] ) ) {
        return;
    }

    $defaults = wp_floating_cta_defaults();

    // ── ボタン インラインスタイル ──────────────────────────────
    $btn_style = sprintf(
        'background-color:%s;color:%s;border-radius:%dpx;font-size:%dpx;padding:%dpx %dpx;',
        esc_attr( $s['bg_color']      ?? $defaults['bg_color'] ),
        esc_attr( $s['text_color']    ?? $defaults['text_color'] ),
        absint(   $s['border_radius'] ?? $defaults['border_radius'] ),
        absint(   $s['font_size']     ?? $defaults['font_size'] ),
        absint(   $s['padding_v']     ?? $defaults['padding_v'] ),
        absint(   $s['padding_h']     ?? $defaults['padding_h'] )
    );

    // ── ラッパー 位置スタイル ────────────────────────────────
    $full_width = ! empty( $s['full_width'] ?? '' );

    if ( $full_width ) {
        $wrap_style = 'bottom:0;left:0;right:0;transform:none;max-width:100%;';
    } else {
        $position_css = 'left:50%;transform:translateX(-50%);';
        if ( ( $s['position'] ?? 'center' ) === 'left' ) {
            $position_css = 'left:20px;right:auto;transform:none;';
        } elseif ( ( $s['position'] ?? 'center' ) === 'right' ) {
            $position_css = 'right:20px;left:auto;transform:none;';
        }
        $wrap_style = sprintf(
            'bottom:%dpx;max-width:%dpx;%s',
            absint( $s['bottom_offset'] ?? $defaults['bottom_offset'] ),
            absint( $s['max_width']     ?? $defaults['max_width'] ),
            $position_css
        );
    }
    $wrap_style .= sprintf(
        'padding:%dpx %dpx;',
        absint( $s['bg_padding_v'] ?? $defaults['bg_padding_v'] ),
        absint( $s['bg_padding_h'] ?? $defaults['bg_padding_h'] )
    );

    // ── クラス ─────────────────────────────────────────────
    $shadow_class    = ! empty( $s['shadow']     ?? '' ) ? ' fcta-shadow' : '';
    $fullwidth_class = $full_width ? ' fcta-fullwidth' : '';
    $animation_class = 'fcta-anim-' . esc_attr( $s['animation'] ?? 'slide' );

    $valid_anims    = [ 'float', 'shine', 'pulse', 'none' ];
    $btn_anim       = in_array( $s['button_animation'] ?? 'none', $valid_anims, true )
                          ? ( $s['button_animation'] ?? 'none' ) : 'none';
    $btn_anim_class = ( $btn_anim !== 'none' ) ? ' fcta-btn-' . esc_attr( $btn_anim ) : '';
    $btn_3d_class   = ! empty( $s['button_3d'] ?? '' ) ? ' fcta-btn-3d' : '';

    // ── リンク属性 ──────────────────────────────────────────
    $target = ( ( $s['button_target'] ?? '_blank' ) === '_self' ) ? '_self' : '_blank';
    $rel    = ( $target === '_blank' ) ? ' rel="noopener noreferrer"' : '';

    // ── 内側ラッパースタイル（フルワイド時のみ max-width 制約） ──
    $inner_style = $full_width
        ? sprintf( 'max-width:%dpx;width:100%%;margin:0 auto;', absint( $s['max_width'] ?? $defaults['max_width'] ) )
        : '';

    ?>
    <div id="wp-floating-cta"
         class="wp-floating-cta <?php echo esc_attr( $animation_class . $shadow_class . $fullwidth_class ); ?>"
         style="<?php echo esc_attr( $wrap_style ); ?>"
         role="complementary" aria-label="CTAボタン">

        <?php if ( ! empty( $s['show_close'] ?? '' ) ) : ?>
        <button class="fcta-close" aria-label="閉じる" title="閉じる">&#x2715;</button>
        <?php endif; ?>

        <div class="fcta-inner" <?php echo $inner_style ? 'style="' . esc_attr( $inner_style ) . '"' : ''; ?>>

            <?php if ( trim( $s['micro_copy_top'] ?? '' ) !== '' ) : ?>
            <p class="fcta-micro fcta-micro-top"
               style="color:<?php echo esc_attr( $s['micro_top_color'] ?? $defaults['micro_top_color'] ); ?>;font-size:<?php echo absint( $s['micro_top_size'] ?? $defaults['micro_top_size'] ); ?>px;">
                <?php echo wp_kses_post( $s['micro_copy_top'] ); ?>
            </p>
            <?php endif; ?>

            <a href="<?php echo esc_url( $s['button_url'] ); ?>"
               target="<?php echo esc_attr( $target ); ?>"<?php echo $rel; ?>
               class="fcta-btn<?php echo esc_attr( $btn_anim_class . $btn_3d_class ); ?>"
               style="<?php echo esc_attr( $btn_style ); ?>">
                <?php echo wp_kses_post( $s['button_text'] ?? $defaults['button_text'] ); ?>
            </a>

            <?php if ( trim( $s['micro_copy_bottom'] ?? '' ) !== '' ) : ?>
            <p class="fcta-micro fcta-micro-bottom"
               style="color:<?php echo esc_attr( $s['micro_bottom_color'] ?? $defaults['micro_bottom_color'] ); ?>;font-size:<?php echo absint( $s['micro_bottom_size'] ?? $defaults['micro_bottom_size'] ); ?>px;">
                <?php echo wp_kses_post( $s['micro_copy_bottom'] ); ?>
            </p>
            <?php endif; ?>

        </div>
    </div>
    <?php
}
