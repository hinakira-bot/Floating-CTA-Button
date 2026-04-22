( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var cta = document.getElementById( 'wp-floating-cta' );
        if ( ! cta ) return;

        var closeBtn = cta.querySelector( '.fcta-close' );
        if ( closeBtn ) {
            closeBtn.addEventListener( 'click', function () {
                cta.classList.add( 'fcta-hidden' );
            } );
        }
    } );
} )();
