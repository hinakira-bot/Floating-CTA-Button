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
    $out['full_width']        = ! empty( $input['full_width'] ) ? '1' : '';
    $out['button_3d']         = ! empty( $input['button_3d'] ) ? '1' : '';
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

    $int_fields = [
        'border_radius', 'font_size', 'padding_v', 'padding_h',
        'bottom_offset', 'max_width', 'micro_top_size', 'micro_bottom_size',
        'bg_padding_v', 'bg_padding_h',
        'mobile_font_size', 'mobile_padding_v', 'mobile_padding_h',
        'mobile_bg_padding_v', 'mobile_bg_padding_h',
    ];
    foreach ( $int_fields as $field ) {
        $out[ $field ] = (string) absint( $input[ $field ] ?? $defaults[ $field ] );
    }

    $out['position']         = in_array( $input['position'] ?? '', [ 'left', 'center', 'right' ], true )
                                   ? $input['position'] : 'center';
    $out['animation']        = in_array( $input['animation'] ?? '', [ 'slide', 'fade', 'none' ], true )
                                   ? $input['animation'] : 'slide';
    $out['button_animation'] = in_array( $input['button_animation'] ?? '', [ 'none', 'float', 'shine', 'pulse' ], true )
                                   ? $input['button_animation'] : 'none';

    return $out;
}

/* ========================================================
   設定ページ HTML
   ======================================================== */
function wp_floating_cta_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $s   = wp_floating_cta_get_settings();
    $opt = WP_FLOATING_CTA_OPTION;

    // プレビュー用の初期クラス計算
    $prev_shadow   = $s['shadow']     ? ' fcta-shadow' : '';
    $prev_fullw    = $s['full_width'] ? ' fcta-fullwidth' : '';
    $prev_3d       = $s['button_3d'] ? ' fcta-btn-3d' : '';
    $prev_btn_anim = ( $s['button_animation'] !== 'none' ) ? ' fcta-btn-' . $s['button_animation'] : '';
    ?>
    <div class="wrap">
        <h1>🎯 Floating CTA ボタン 設定</h1>
        <?php settings_errors( WP_FLOATING_CTA_OPTION ); ?>

        <style>
        .fcta-section {
            background:#fff; border:1px solid #ccd0d4;
            border-radius:6px; padding:20px 24px; margin-bottom:20px;
        }
        .fcta-section h2 {
            margin-top:0; padding-bottom:10px; border-bottom:1px solid #eee; font-size:15px;
        }

        /* ── プレビューフレーム ── */
        #fcta-preview-frame {
            background: linear-gradient(160deg, #dde3ec 0%, #c8d0db 100%);
            border-radius:8px;
            min-height:160px;
            display:flex;
            align-items:flex-end;
            justify-content:center;
            padding:20px 16px 16px;
            position:relative;
            overflow:hidden;
            transition: max-width 0.3s, margin 0.3s;
        }
        #fcta-preview-frame::before {
            content:'ウェブサイトのコンテンツ';
            position:absolute; top:38%; left:50%;
            transform:translate(-50%,-50%);
            color:#b0b8c4; font-size:12px; letter-spacing:.05em; pointer-events:none;
        }
        /* ウィジェット本体 */
        #fcta-preview-widget {
            background:rgba(255,255,255,.96);
            border-radius:12px;
            display:flex; flex-direction:column;
            align-items:center; gap:6px;
            width:100%;
            position:relative; box-sizing:border-box;
            transition: max-width 0.3s;
        }
        #fcta-preview-widget.fcta-shadow  { box-shadow:0 4px 20px rgba(0,0,0,.18); }
        #fcta-preview-widget.fcta-fullwidth { max-width:100% !important; border-radius:0; }

        /* PC / スマホ トグル */
        #fcta-view-toggle { display:flex; gap:6px; }
        #fcta-view-toggle .button { min-width:80px; }
        </style>

        <form method="post" action="options.php">
            <?php settings_fields( 'wp_floating_cta_group' ); ?>

            <!-- ===== ライブプレビュー ===== -->
            <div class="fcta-section">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                    <h2 style="margin:0;padding:0;border:0;">👁 ライブプレビュー</h2>
                    <div id="fcta-view-toggle">
                        <button type="button" id="fcta-view-pc" class="button button-primary">🖥 PC</button>
                        <button type="button" id="fcta-view-sp" class="button">📱 スマホ</button>
                    </div>
                </div>

                <div id="fcta-preview-frame">
                    <div id="fcta-preview-widget"
                         class="<?php echo esc_attr( trim( $prev_shadow . $prev_fullw ) ); ?>"
                         style="max-width:<?php echo esc_attr( $s['full_width'] ? '100%' : $s['max_width'] . 'px' ); ?>;padding:<?php echo (int)$s['bg_padding_v']; ?>px <?php echo (int)$s['bg_padding_h']; ?>px;">

                        <button type="button" class="fcta-close"
                                style="<?php echo $s['show_close'] ? '' : 'display:none;'; ?>">&#x2715;</button>

                        <p class="fcta-micro fcta-micro-top" id="fcta-prev-micro-top"
                           style="color:<?php echo esc_attr($s['micro_top_color']); ?>;font-size:<?php echo (int)$s['micro_top_size']; ?>px;<?php echo trim($s['micro_copy_top'])==='' ? 'display:none;' : ''; ?>">
                            <?php echo esc_html( $s['micro_copy_top'] ); ?></p>

                        <span id="fcta-prev-btn"
                              class="fcta-btn<?php echo esc_attr( $prev_btn_anim . $prev_3d ); ?>"
                              style="background-color:<?php echo esc_attr($s['bg_color']); ?>;color:<?php echo esc_attr($s['text_color']); ?>;border-radius:<?php echo (int)$s['border_radius']; ?>px;font-size:<?php echo (int)$s['font_size']; ?>px;padding:<?php echo (int)$s['padding_v']; ?>px <?php echo (int)$s['padding_h']; ?>px;cursor:default;">
                            <?php echo esc_html( $s['button_text'] ); ?></span>

                        <p class="fcta-micro fcta-micro-bottom" id="fcta-prev-micro-bot"
                           style="color:<?php echo esc_attr($s['micro_bottom_color']); ?>;font-size:<?php echo (int)$s['micro_bottom_size']; ?>px;<?php echo trim($s['micro_copy_bottom'])==='' ? 'display:none;' : ''; ?>">
                            <?php echo esc_html( $s['micro_copy_bottom'] ); ?></p>
                    </div>
                </div>
                <p class="description" style="margin-top:8px;font-size:11px;">
                    ※ プレビューはイメージです。実際の表示はサイトのフォントやCSSに依存します。<br>
                    ※ ボタンが消えた場合は <strong>シークレットウィンドウ</strong> で確認してください（×ボタンを押した記録が残るため）。
                </p>
            </div>

            <!-- ===== 基本設定 ===== -->
            <div class="fcta-section">
                <h2>基本設定</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">有効化</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $opt; ?>[enabled]"
                                       value="1" <?php checked( $s['enabled'], '1' ); ?>>
                                フローティングCTAを表示する
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-btn-text">ボタンテキスト</label></th>
                        <td>
                            <input type="text" id="fcta-btn-text"
                                   name="<?php echo $opt; ?>[button_text]"
                                   value="<?php echo esc_attr( $s['button_text'] ); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-btn-url">リンクURL</label></th>
                        <td>
                            <input type="url" id="fcta-btn-url"
                                   name="<?php echo $opt; ?>[button_url]"
                                   value="<?php echo esc_attr( $s['button_url'] ); ?>"
                                   class="regular-text" placeholder="https://">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">リンクの開き方</th>
                        <td>
                            <select name="<?php echo $opt; ?>[button_target]">
                                <option value="_blank" <?php selected( $s['button_target'], '_blank' ); ?>>新しいタブで開く</option>
                                <option value="_self"  <?php selected( $s['button_target'], '_self' ); ?>>同じタブで開く</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">閉じるボタン (×)</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $opt; ?>[show_close]"
                                       value="1" <?php checked( $s['show_close'], '1' ); ?>>
                                表示する（セッション中は一度閉じると非表示）
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== マイクロコピー ===== -->
            <div class="fcta-section">
                <h2>マイクロコピー</h2>
                <p class="description">ボタンの上下に表示する補足テキスト。不要なら空欄に。</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="fcta-micro-top">ボタン上のコピー</label></th>
                        <td>
                            <input type="text" id="fcta-micro-top"
                                   name="<?php echo $opt; ?>[micro_copy_top]"
                                   value="<?php echo esc_attr( $s['micro_copy_top'] ); ?>"
                                   class="regular-text" placeholder="例: ✨ 期間限定キャンペーン中">
                            <div style="display:flex;gap:12px;margin-top:8px;align-items:center;flex-wrap:wrap;">
                                <label>文字色 <input type="color" name="<?php echo $opt; ?>[micro_top_color]" value="<?php echo esc_attr( $s['micro_top_color'] ); ?>"></label>
                                <label>サイズ <input type="number" name="<?php echo $opt; ?>[micro_top_size]" value="<?php echo esc_attr( $s['micro_top_size'] ); ?>" min="10" max="24" style="width:65px;"> px</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fcta-micro-bottom">ボタン下のコピー</label></th>
                        <td>
                            <input type="text" id="fcta-micro-bottom"
                                   name="<?php echo $opt; ?>[micro_copy_bottom]"
                                   value="<?php echo esc_attr( $s['micro_copy_bottom'] ); ?>"
                                   class="regular-text" placeholder="例: ⏰ お申し込みはお早めに">
                            <div style="display:flex;gap:12px;margin-top:8px;align-items:center;flex-wrap:wrap;">
                                <label>文字色 <input type="color" name="<?php echo $opt; ?>[micro_bottom_color]" value="<?php echo esc_attr( $s['micro_bottom_color'] ); ?>"></label>
                                <label>サイズ <input type="number" name="<?php echo $opt; ?>[micro_bottom_size]" value="<?php echo esc_attr( $s['micro_bottom_size'] ); ?>" min="10" max="24" style="width:65px;"> px</label>
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
                            <label>背景色 <input type="color" name="<?php echo $opt; ?>[bg_color]" value="<?php echo esc_attr( $s['bg_color'] ); ?>"></label>
                            <label>文字色 <input type="color" name="<?php echo $opt; ?>[text_color]" value="<?php echo esc_attr( $s['text_color'] ); ?>"></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">立体的なボタン</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $opt; ?>[button_3d]"
                                       value="1" id="fcta-btn-3d" <?php checked( $s['button_3d'], '1' ); ?>>
                                グラデーション＋影でボタンを立体的に見せる
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">角丸</th>
                        <td>
                            <input type="number" name="<?php echo $opt; ?>[border_radius]"
                                   value="<?php echo esc_attr( $s['border_radius'] ); ?>"
                                   min="0" max="100" style="width:80px;"> px
                            <span class="description">（0=角なし / 50=丸ボタン）</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">フォントサイズ</th>
                        <td>
                            <input type="number" name="<?php echo $opt; ?>[font_size]"
                                   value="<?php echo esc_attr( $s['font_size'] ); ?>"
                                   min="12" max="36" style="width:80px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ボタン内側余白</th>
                        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <label>上下 <input type="number" name="<?php echo $opt; ?>[padding_v]" value="<?php echo esc_attr( $s['padding_v'] ); ?>" min="4" max="60" style="width:65px;"> px</label>
                            <label>左右 <input type="number" name="<?php echo $opt; ?>[padding_h]" value="<?php echo esc_attr( $s['padding_h'] ); ?>" min="8" max="120" style="width:65px;"> px</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ボタンアニメーション</th>
                        <td>
                            <select name="<?php echo $opt; ?>[button_animation]">
                                <option value="none"  <?php selected( $s['button_animation'], 'none' ); ?>>なし</option>
                                <option value="float" <?php selected( $s['button_animation'], 'float' ); ?>>ふわふわ（上下に揺れる）</option>
                                <option value="shine" <?php selected( $s['button_animation'], 'shine' ); ?>>きらり（光が走る）</option>
                                <option value="pulse" <?php selected( $s['button_animation'], 'pulse' ); ?>>ドキドキ（拡大縮小）</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ドロップシャドウ</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $opt; ?>[shadow]"
                                       value="1" <?php checked( $s['shadow'], '1' ); ?>>
                                パネルに影をつける
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== パネル背景・余白 ===== -->
            <div class="fcta-section">
                <h2>パネル背景・余白</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">パネル内側余白</th>
                        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <label>上下 <input type="number" name="<?php echo $opt; ?>[bg_padding_v]" value="<?php echo esc_attr( $s['bg_padding_v'] ); ?>" min="0" max="60" style="width:65px;"> px</label>
                            <label>左右 <input type="number" name="<?php echo $opt; ?>[bg_padding_h]" value="<?php echo esc_attr( $s['bg_padding_h'] ); ?>" min="0" max="80" style="width:65px;"> px</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">横幅いっぱい</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $opt; ?>[full_width]"
                                       value="1" id="fcta-fullwidth-chk"
                                       <?php checked( $s['full_width'], '1' ); ?>>
                                画面横幅いっぱいに表示する（最大幅・位置設定を無視）
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== モバイル設定 ===== -->
            <div class="fcta-section">
                <h2>📱 モバイル設定 <span style="font-size:12px;font-weight:normal;color:#666;">（〜767px に適用）</span></h2>
                <p class="description">PC設定を引き継ぐ項目は 0 にしてください。</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">フォントサイズ</th>
                        <td>
                            <input type="number" name="<?php echo $opt; ?>[mobile_font_size]"
                                   value="<?php echo esc_attr( $s['mobile_font_size'] ); ?>"
                                   min="0" max="36" style="width:80px;"> px
                            <span class="description">（0 = PC設定と同じ）</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ボタン内側余白</th>
                        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <label>上下 <input type="number" name="<?php echo $opt; ?>[mobile_padding_v]" value="<?php echo esc_attr( $s['mobile_padding_v'] ); ?>" min="0" max="60" style="width:65px;"> px</label>
                            <label>左右 <input type="number" name="<?php echo $opt; ?>[mobile_padding_h]" value="<?php echo esc_attr( $s['mobile_padding_h'] ); ?>" min="0" max="120" style="width:65px;"> px</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">パネル内側余白</th>
                        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                            <label>上下 <input type="number" name="<?php echo $opt; ?>[mobile_bg_padding_v]" value="<?php echo esc_attr( $s['mobile_bg_padding_v'] ); ?>" min="0" max="60" style="width:65px;"> px</label>
                            <label>左右 <input type="number" name="<?php echo $opt; ?>[mobile_bg_padding_h]" value="<?php echo esc_attr( $s['mobile_bg_padding_h'] ); ?>" min="0" max="80" style="width:65px;"> px</label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ===== 位置・表示設定 ===== -->
            <div class="fcta-section" id="fcta-position-section">
                <h2>位置・表示設定</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">表示位置（横）</th>
                        <td>
                            <select name="<?php echo $opt; ?>[position]">
                                <option value="left"   <?php selected( $s['position'], 'left' ); ?>>左</option>
                                <option value="center" <?php selected( $s['position'], 'center' ); ?>>中央</option>
                                <option value="right"  <?php selected( $s['position'], 'right' ); ?>>右</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">下からの距離</th>
                        <td>
                            <input type="number" name="<?php echo $opt; ?>[bottom_offset]"
                                   value="<?php echo esc_attr( $s['bottom_offset'] ); ?>"
                                   min="0" max="200" style="width:80px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">最大幅</th>
                        <td>
                            <input type="number" name="<?php echo $opt; ?>[max_width]"
                                   value="<?php echo esc_attr( $s['max_width'] ); ?>"
                                   min="200" max="1200" style="width:90px;"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">登場アニメーション</th>
                        <td>
                            <select name="<?php echo $opt; ?>[animation]">
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

    <script>
    /* フルワイドONのとき位置設定セクションをグレーアウト */
    (function(){
        var chk = document.getElementById('fcta-fullwidth-chk');
        var sec = document.getElementById('fcta-position-section');
        if(!chk||!sec) return;
        function toggle(){ sec.style.opacity=chk.checked?'.4':'1'; sec.style.pointerEvents=chk.checked?'none':''; }
        chk.addEventListener('change', toggle);
        toggle();
    })();
    </script>
    <?php
}
