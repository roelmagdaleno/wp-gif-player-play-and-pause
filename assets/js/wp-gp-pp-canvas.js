/**
 * Get all GIFs and load them when the DOM is ready.
 *
 * After every GIF is loaded then set a callback to setup the
 * clickable action into the overlay.
 *
 * @since 0.1.0
 */
function WP_GP_PP_initGIFCanvas() {
    const gifs = document.querySelectorAll( '.wp-gp-pp-gif-canvas-player' );

    for ( let i = 0; i < gifs.length; i++ ) {
        const overlay  = gifs[i].nextElementSibling;
        const superGif = new SuperGif( { gif: gifs[i] } );

        superGif.load( () => WP_GP_PP_toggleGIFCanvas( overlay, superGif ) );
    }
}

/**
 * Play or Pause the clicked GIF.
 *
 * When the user clicks on the GIF it will remove the GIF
 * icon and when the users clicks again it will appear.
 *
 * The clickable element is the overlay.
 *
 * @since 0.1.0
 *
 * @param {Element}   overlay    The clicked GIF overlay HTML element.
 * @param {object}    superGif   The clicked GIF element.
 */
function WP_GP_PP_toggleGIFCanvas( overlay, superGif ) {
    /**
     * For some reason the function "get_playing" returns
     * true when the GIF finish its loading, that's why we
     * have to pause it before click.
     *
     * @since 0.1.0
     */
    if ( ! superGif.get_auto_play() ) {
        superGif.pause();
    }

    overlay.onclick = () => {
        const button    = overlay.children[0];
        const isPlaying = superGif.get_playing();

        isPlaying ? button.classList.remove( 'is-playing' ) : button.classList.add( 'is-playing' );
        isPlaying ? superGif.pause() : superGif.play();
    }
}

document.addEventListener( 'DOMContentLoaded', WP_GP_PP_initGIFCanvas );
