/* Floating CTA — ライブプレビュー */
( function () {
    'use strict';

    var OPT = 'wp_floating_cta_settings';

    function field( key ) {
        var el = document.querySelector( '[name="' + OPT + '[' + key + ']"]' );
        if ( ! el ) return '';
        if ( el.type === 'checkbox' ) return el.checked ? '1' : '';
        return el.value;
    }

    function updatePreview() {
        var widget  = document.getElementById( 'fcta-preview-widget' );
        var btn     = document.getElementById( 'fcta-prev-btn' );
        var mTop    = document.getElementById( 'fcta-prev-micro-top' );
        var mBot    = document.getElementById( 'fcta-prev-micro-bot' );
        var closeEl = widget ? widget.querySelector( '.fcta-close' ) : null;
        if ( ! widget || ! btn ) return;

        /* ── ボタンスタイル ── */
        btn.style.backgroundColor = field( 'bg_color' )     || '#ff6b35';
        btn.style.color            = field( 'text_color' )   || '#ffffff';
        btn.style.borderRadius     = ( field( 'border_radius' ) || '8' )  + 'px';
        btn.style.fontSize         = ( field( 'font_size' )     || '18' ) + 'px';
        btn.style.padding          =
            ( field( 'padding_v' ) || '16' ) + 'px ' +
            ( field( 'padding_h' ) || '32' ) + 'px';
        btn.textContent = field( 'button_text' ) || '（テキスト未入力）';

        /* ── ボタン常時アニメーション ── */
        [ 'fcta-btn-float', 'fcta-btn-shine', 'fcta-btn-pulse' ].forEach( function ( c ) {
            btn.classList.remove( c );
        } );
        var btnAnim = field( 'button_animation' );
        if ( btnAnim && btnAnim !== 'none' ) {
            btn.classList.add( 'fcta-btn-' + btnAnim );
        }

        /* ── パネル余白 ── */
        var bpv = field( 'bg_padding_v' ) || '12';
        var bph = field( 'bg_padding_h' ) || '16';
        widget.style.padding = bpv + 'px ' + bph + 'px';

        /* ── フルワイド ── */
        if ( field( 'full_width' ) ) {
            widget.classList.add( 'fcta-fullwidth' );
        } else {
            widget.classList.remove( 'fcta-fullwidth' );
        }

        /* ── シャドウ ── */
        if ( field( 'shadow' ) ) {
            widget.classList.add( 'fcta-shadow' );
        } else {
            widget.classList.remove( 'fcta-shadow' );
        }

        /* ── 閉じるボタン ── */
        if ( closeEl ) {
            closeEl.style.display = field( 'show_close' ) ? '' : 'none';
        }

        /* ── マイクロコピー 上 ── */
        if ( mTop ) {
            var topText = field( 'micro_copy_top' );
            mTop.textContent  = topText;
            mTop.style.color     = field( 'micro_top_color' ) || '#333';
            mTop.style.fontSize  = ( field( 'micro_top_size' ) || '13' ) + 'px';
            mTop.style.display   = topText.trim() ? '' : 'none';
        }

        /* ── マイクロコピー 下 ── */
        if ( mBot ) {
            var botText = field( 'micro_copy_bottom' );
            mBot.textContent  = botText;
            mBot.style.color     = field( 'micro_bottom_color' ) || '#666';
            mBot.style.fontSize  = ( field( 'micro_bottom_size' ) || '12' ) + 'px';
            mBot.style.display   = botText.trim() ? '' : 'none';
        }
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        var form = document.querySelector( 'form' );
        if ( ! form ) return;
        form.addEventListener( 'input',  updatePreview );
        form.addEventListener( 'change', updatePreview );
        updatePreview();
    } );
} )();
