( function () {
    'use strict';

    var STORAGE_KEY = 'wp_fcta_closed';

    document.addEventListener( 'DOMContentLoaded', function () {
        var cta   = document.getElementById( 'wp-floating-cta' );
        if ( ! cta ) return;

        // セッション中に閉じられていたら非表示
        if ( sessionStorage.getItem( STORAGE_KEY ) ) {
            cta.classList.add( 'fcta-hidden' );
            return;
        }

        // 閉じるボタン
        var closeBtn = cta.querySelector( '.fcta-close' );
        if ( closeBtn ) {
            closeBtn.addEventListener( 'click', function () {
                cta.classList.add( 'fcta-hidden' );
                sessionStorage.setItem( STORAGE_KEY, '1' );
            } );
        }
    } );
} )();
