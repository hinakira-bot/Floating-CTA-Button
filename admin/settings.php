<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'wp_floating_cta_admin_menu' );
function wp_floating_cta_admin_menu(): void {
    add_options_page(
        'Floating CTA 設定',
        'Floating CTA',
        'manage_options',
        'wp-floating-cta',
        'wp_floating_cta_settings_page'
    );
}

add_action( 'admin_init', 'wp_floating_cta_register_settings' );
function wp_floating_cta_register_settings(): void {
    register_setting(
        'wp_floating_cta_group',
        WP_FLOATING_CTA_OPTION,
        [
            'sanitize_callback' => 'wp_floating_cta_sanitize',
            'default'           => wp_floating_cta_defaults(),
        ]
    );
}

function wp_floating_cta_sanitize( mixed $input ): array {
    $defaults = wp_floating_cta_defaults();
    $out      = [];

    $out['enabled']           = ! empty( $input['enabled'] ) ? '1' : '';
    $out['show_close']        = ! empty( $input['show_close'] ) ? '1' : '';
    $out['shadow']            = ! empty( $input['shadow'] ) ? '1' : '';
    $out['button_text']       = sanitize_text_field( $input['button_text'] ?? $defaults['button_text'] );
    $out['button_url']        = esc_url_raw( $input['button_url'] ?? '' );
    $out['button_target']     = in_array( $input['button_target'] ?? '', [ '_blank', '_self' ], true )
                                    ? $input['button_target'] : '_blank';
    $out['micro_copy_top']    = sanitize_text_field( $input['micro_copy_top'] ?? '' );
    $out['micro_copy_bottom'] = sanitize_text_field( $input['micro_copy_bottom'] ?? '' );
    $out['bg_color']          = sanitize_hex_color( $input['bg_color'] ?? $defaults['bg_color'] ) ?: $defaults['bg_color'];
    $out['text_color']        = sanitize_hex_color( $input['text_color'] ?? $defaults['text_color'] ) ?: $defaults['text_color'];
    $out['micro_top_color']   = sanitize_hex_color( $input['micro_top_color'] ?? $defaults['micro_top_color'] ) ?: $defaults['micro_top_color'];
    $out['micro_bottom_color']= sanitize_hex_color( $input['micro_bottom_color'] ?? $defaults['micro_bottom_color'] ) ?: $defaults['micro_bottom_color'];

    $int_fields = [ 'border_radius', 'font_size', 'padding_v', 'padding_h', 'bottom_offset', 'max_width', 'micro_top_size', 'micro_bottom_size' ];
    foreach ( $int_fields as $field ) {
        $out[ $field ] = (string) absint( $input[ $field ] ?? $defaults[ $field ] );
    }

    $out['position']  = in_array( $input['position'] ?? '', [ 'left', 'center', 'right' ], true )
                            ? $input['position'] : 'center';
    $out['animation'] = in_array( $input['animation'] ?? '', [ 'slide', 'fade', 'none' ], true )
                            ? $input['animation'] : 'slide';

    return $out;
}

function wp_floating_cta_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $s = wp_floating_cta_get_settings();
    ?>
    <div class="wrap">
        <h1>🎯 Floating CTA ボタン 設定</h1>

        <?php settings_errors( WP_FLOATING_CTA_OPTION ); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'wp_floating_cta_group' ); ?>

            <style>
                .fcta-section { background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:20px 24px; margin-bottom:20px; }
                .fcta-section h2 { margin-top:0; padding-bottom:10px; border-bottom:1px solid #eee; }
                .fcta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
                .fcta-field label { display:block; font-weight:600; margin-bottom:4px; }
                .fcta-field input[type=text],
                .fcta-field input[type=url],
                .fcta-field input[type=number],
                .fcta-field select { width:100%; }
                .fcta-preview-wrap { margin-top:20px; }
                .fcta-preview-btn {
                    display:inline-block; padding:16px 32px; font-size:18px;
                    border-radius:8px; border:none; cursor:pointer;
                    background:#ff6b35; color:#fff; font-weight:bold;
                }
                @media(max-width:700px){ .fcta-grid{ grid-template-columns:1fr; } }
            </style>

            <!-- ===== 基本設定 ===== -->
            <div class="fcta-section">
                <h2>基本設定</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">有効化</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[enabled]"
                                       value="1" <?php checked( $s['enabled'], '1' ); ?>>
                                フローティングCTAを表示する
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-btn-text">ボタンテキスト</label></th>
                        <td>
                            <input type="text" id="fcta-btn-text"
                                   name="<?php echo WP_FLOATING_CTA_OPTION; ?>[button_text]"
                                   value="<?php echo esc_attr( $s['button_text'] ); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-btn-url">リンクURL</label></th>
                        <td>
                            <input type="url" id="fcta-btn-url"
                                   name="<?php echo WP_FLOATING_CTA_OPTION; ?>[button_url]"
                                   value="<?php echo esc_attr( $s['button_url'] ); ?>"
                                   class="regular-text" placeholder="https://">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">リンクの開き方</th>
                        <td>
                            <select name="<?php echo WP_FLOATING_CTA_OPTION; ?>[button_target]">
                                <option value="_blank" <?php selected( $s['button_target'], '_blank' ); ?>>新しいタブで開く</option>
                                <option value="_self"  <?php selected( $s['button_target'], '_self' ); ?>>同じタブで開く</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">閉じるボタン (×)</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[show_close]"
                                       value="1" <?php checked( $s['show_close'], '1' ); ?>>
                                表示する（一度閉じるとセッション中は非表示）
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== マイクロコピー ===== -->
            <div class="fcta-section">
                <h2>マイクロコピー</h2>
                <p class="description">ボタンの上下に表示する補足テキストです。不要な場合は空欄にしてください。</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="fcta-micro-top">ボタン上のコピー</label></th>
                        <td>
                            <input type="text" id="fcta-micro-top"
                                   name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_copy_top]"
                                   value="<?php echo esc_attr( $s['micro_copy_top'] ); ?>"
                                   class="regular-text" placeholder="例: ✨ 期間限定キャンペーン中">
                            <div style="display:flex;gap:12px;margin-top:8px;align-items:center;">
                                <label>文字色
                                    <input type="color" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_top_color]"
                                           value="<?php echo esc_attr( $s['micro_top_color'] ); ?>">
                                </label>
                                <label>サイズ
                                    <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_top_size]"
                                           value="<?php echo esc_attr( $s['micro_top_size'] ); ?>"
                                           min="10" max="24" style="width:70px;">px
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-micro-bottom">ボタン下のコピー</label></th>
                        <td>
                            <input type="text" id="fcta-micro-bottom"
                                   name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_copy_bottom]"
                                   value="<?php echo esc_attr( $s['micro_copy_bottom'] ); ?>"
                                   class="regular-text" placeholder="例: ⏰ お申し込みはお早めに">
                            <div style="display:flex;gap:12px;margin-top:8px;align-items:center;">
                                <label>文字色
                                    <input type="color" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_bottom_color]"
                                           value="<?php echo esc_attr( $s['micro_bottom_color'] ); ?>">
                                </label>
                                <label>サイズ
                                    <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[micro_bottom_size]"
                                           value="<?php echo esc_attr( $s['micro_bottom_size'] ); ?>"
                                           min="10" max="24" style="width:70px;">px
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== デザイン設定 ===== -->
            <div class="fcta-section">
                <h2>デザイン設定</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">ボタンの色</th>
                        <td style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
                            <label>背景色
                                <input type="color" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[bg_color]"
                                       value="<?php echo esc_attr( $s['bg_color'] ); ?>">
                            </label>
                            <label>文字色
                                <input type="color" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[text_color]"
                                       value="<?php echo esc_attr( $s['text_color'] ); ?>">
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">角丸 (border-radius)</th>
                        <td>
                            <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[border_radius]"
                                   value="<?php echo esc_attr( $s['border_radius'] ); ?>"
                                   min="0" max="100" style="width:80px;"> px
                            <span class="description">（0=角なし / 50=丸ボタン）</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">フォントサイズ</th>
                        <td>
                            <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[font_size]"
                                   value="<?php echo esc_attr( $s['font_size'] ); ?>"
                                   min="12" max="36" style="width:80px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">パディング</th>
                        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <label>上下
                                <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[padding_v]"
                                       value="<?php echo esc_attr( $s['padding_v'] ); ?>"
                                       min="4" max="60" style="width:70px;"> px
                            </label>
                            <label>左右
                                <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[padding_h]"
                                       value="<?php echo esc_attr( $s['padding_h'] ); ?>"
                                       min="8" max="120" style="width:70px;"> px
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ドロップシャドウ</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[shadow]"
                                       value="1" <?php checked( $s['shadow'], '1' ); ?>>
                                影をつける
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== 位置・表示設定 ===== -->
            <div class="fcta-section">
                <h2>位置・表示設定</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">表示位置（横）</th>
                        <td>
                            <select name="<?php echo WP_FLOATING_CTA_OPTION; ?>[position]">
                                <option value="left"   <?php selected( $s['position'], 'left' ); ?>>左</option>
                                <option value="center" <?php selected( $s['position'], 'center' ); ?>>中央</option>
                                <option value="right"  <?php selected( $s['position'], 'right' ); ?>>右</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">下からの距離</th>
                        <td>
                            <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[bottom_offset]"
                                   value="<?php echo esc_attr( $s['bottom_offset'] ); ?>"
                                   min="0" max="200" style="width:80px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">最大幅</th>
                        <td>
                            <input type="number" name="<?php echo WP_FLOATING_CTA_OPTION; ?>[max_width]"
                                   value="<?php echo esc_attr( $s['max_width'] ); ?>"
                                   min="200" max="1200" style="width:90px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">表示アニメーション</th>
                        <td>
                            <select name="<?php echo WP_FLOATING_CTA_OPTION; ?>[animation]">
                                <option value="slide" <?php selected( $s['animation'], 'slide' ); ?>>スライドアップ</option>
                                <option value="fade"  <?php selected( $s['animation'], 'fade' ); ?>>フェードイン</option>
                                <option value="none"  <?php selected( $s['animation'], 'none' ); ?>>なし</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button( '設定を保存する' ); ?>
        </form>
    </div>
    <?php
}
