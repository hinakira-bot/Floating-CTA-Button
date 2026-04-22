/* Floating CTA — ライブプレビュー */
( function () {
    'use strict';

    var OPT     = 'wp_floating_cta_settings';
    var isMobile = false;

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

        /* ── ウィジェット最大幅（PCモード時は設定に従う） ── */
        var fullW = field( 'full_width' );
        if ( fullW ) {
            widget.classList.add( 'fcta-fullwidth' );
            widget.style.maxWidth = '100%';
        } else {
            widget.classList.remove( 'fcta-fullwidth' );
            widget.style.maxWidth = isMobile ? '100%' : ( ( field( 'max_width' ) || '480' ) + 'px' );
        }

        /* ── ボタンスタイル（PC / モバイル切り替え対応） ── */
        btn.style.backgroundColor = field( 'bg_color' )   || '#ff6b35';
        btn.style.color            = field( 'text_color' ) || '#ffffff';
        btn.style.borderRadius     = ( field( 'border_radius' ) || '8' ) + 'px';

        var fs  = field( 'font_size' )  || '18';
        var mpv = field( 'padding_v' )  || '16';
        var mph = field( 'padding_h' )  || '32';
        if ( isMobile ) {
            var mfs = field( 'mobile_font_size' );
            var mmv = field( 'mobile_padding_v' );
            var mmh = field( 'mobile_padding_h' );
            if ( parseInt( mfs ) > 0 ) fs  = mfs;
            if ( parseInt( mmv ) > 0 ) mpv = mmv;
            if ( parseInt( mmh ) > 0 ) mph = mmh;
        }
        btn.style.fontSize = fs + 'px';
        btn.style.padding  = mpv + 'px ' + mph + 'px';
        btn.textContent    = field( 'button_text' ) || '（テキスト未入力）';

        /* ── 立体ボタン ── */
        if ( field( 'button_3d' ) ) {
            btn.classList.add( 'fcta-btn-3d' );
        } else {
            btn.classList.remove( 'fcta-btn-3d' );
        }

        /* ── ボタン常時アニメーション ── */
        [ 'fcta-btn-float', 'fcta-btn-shine', 'fcta-btn-pulse' ].forEach( function ( c ) {
            btn.classList.remove( c );
        } );
        var btnAnim = field( 'button_animation' );
        if ( btnAnim && btnAnim !== 'none' ) {
            btn.classList.add( 'fcta-btn-' + btnAnim );
        }

        /* ── パネル余白（モバイル切り替え対応） ── */
        var bpv = field( 'bg_padding_v' ) || '12';
        var bph = field( 'bg_padding_h' ) || '16';
        if ( isMobile ) {
            var mbpv = field( 'mobile_bg_padding_v' );
            var mbph = field( 'mobile_bg_padding_h' );
            if ( parseInt( mbpv ) > 0 ) bpv = mbpv;
            if ( parseInt( mbph ) > 0 ) bph = mbph;
        }
        widget.style.padding = bpv + 'px ' + bph + 'px';

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
            mTop.textContent = topText;
            mTop.style.color    = field( 'micro_top_color' ) || '#333';
            mTop.style.fontSize = ( field( 'micro_top_size' ) || '13' ) + 'px';
            mTop.style.display  = topText.trim() ? '' : 'none';
        }

        /* ── マイクロコピー 下 ── */
        if ( mBot ) {
            var botText = field( 'micro_copy_bottom' );
            mBot.textContent = botText;
            mBot.style.color    = field( 'micro_bottom_color' ) || '#666';
            mBot.style.fontSize = ( field( 'micro_bottom_size' ) || '12' ) + 'px';
            mBot.style.display  = botText.trim() ? '' : 'none';
        }
    }

    /* ── PC / スマホ トグル ── */
    function initViewToggle() {
        var btnPc   = document.getElementById( 'fcta-view-pc' );
        var btnSp   = document.getElementById( 'fcta-view-sp' );
        var frame   = document.getElementById( 'fcta-preview-frame' );
        if ( ! btnPc || ! btnSp || ! frame ) return;

        function setPC() {
            isMobile = false;
            frame.style.maxWidth = '';
            frame.style.margin   = '';
            btnPc.classList.add( 'button-primary' );
            btnSp.classList.remove( 'button-primary' );
            updatePreview();
        }
        function setSP() {
            isMobile = true;
            frame.style.maxWidth = '390px';
            frame.style.margin   = '0 auto';
            btnPc.classList.remove( 'button-primary' );
            btnSp.classList.add( 'button-primary' );
            updatePreview();
        }
        btnPc.addEventListener( 'click', setPC );
        btnSp.addEventListener( 'click', setSP );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        var form = document.querySelector( 'form' );
        if ( ! form ) return;
        form.addEventListener( 'input',  updatePreview );
        form.addEventListener( 'change', updatePreview );
        initViewToggle();
        updatePreview();
    } );
} )();
