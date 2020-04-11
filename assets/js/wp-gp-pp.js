/**
 * Get all GIFs and load them when the DOM is ready.
 *
 * After every GIF is loaded then set a callback to setup the
 * clickable action into the overlay.
 *
 * This player method uses the library "libgif.js".
 *
 * @since 0.1.0
 */
function WP_GP_PP_initGIFCanvas() {
    const gifs = document.querySelectorAll( '.wp-gp-pp-gif-canvas-player' );

    for ( let i = 0; i < gifs.length; i++ ) {
        const overlay  = gifs[i].nextElementSibling;
        const superGif = new SuperGif( { gif: gifs[i] } );

        superGif.load( () => WP_GP_PP_toggleCanvasGIF( overlay, superGif ) );
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
function WP_GP_PP_toggleCanvasGIF( overlay, superGif ) {
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

/**
 * Get all GIFs and load them when the DOM is ready.
 *
 * The thumbnail image is used as the video poster and
 * the <video> element is controlled by the overlay click handlers.
 *
 * @since 0.1.0
 */
function WP_GP_PP_toggleVideosGIF() {
    const videos = document.querySelectorAll( '.wp-gp-pp-video-player' );

    for ( let i = 0; i < videos.length; i++ ) {
        const video   = videos[ i ];
        const overlay = video.nextElementSibling;

        overlay.onclick = () => {
            const button    = overlay.children[0];
            const classList = button.classList;
            const isPlaying = classList.contains( 'is-playing' );

            isPlaying ? classList.remove( 'is-playing' ) : classList.add( 'is-playing' );
            isPlaying ? video.pause() : video.play();
        };
    }
}

/**
 * Get all GIFs and load them when the DOM is ready.
 *
 * The original GIF won't be loaded when DOM is ready until the
 * user clicks the thumbnail image.
 *
 * After click the thumbnail the GIF will make one unique request
 * and no more, even if the user clicks it again.
 *
 * @since 0.1.0
 */
function WP_GP_PP_toggleGIF() {
    const thumbnails = document.querySelectorAll( '.wp-gp-pp-gif-thumbnail' );

    for ( let i = 0; i < thumbnails.length; i++ ) {
        const thumbnail = thumbnails[ i ];
        const container = thumbnail.parentElement;
        const overlay   = thumbnail.nextElementSibling.nextElementSibling;

        overlay.onclick = () => {
            const button    = overlay.children[0];
            const classList = button.classList;
            const isPlaying = classList.contains( 'is-playing' );
            let realGifEl   = document.getElementById( thumbnail.id.replace( '--thumbnail', '' ) );

            isPlaying ? classList.remove( 'is-playing' ) : classList.add( 'is-playing' );

            if ( '' === realGifEl.getAttribute( 'src' ) ) {
                let thumbnailImage = thumbnail.getAttribute( 'src' );
                let gifImage       = thumbnailImage.replace( '_gif_thumbnail.jpeg', '.gif' );

                realGifEl.setAttribute( 'src', gifImage );
            }

            isPlaying ? container.classList.remove( 'is-playing' ) : container.classList.add( 'is-playing' );
        };
    }
}

/**
 * Start the GIF loading process but first decide
 * which toggle methods will be loaded according to the
 * current player methods in the post.
 *
 * @since 0.1.0
 */
function WP_GP_PP_INIT() {
    const gifPlayersInPost = WP_GIF_PLAYER.gifPlayersInPost;

    if ( gifPlayersInPost.includes( 'gif' ) ) {
        WP_GP_PP_toggleGIF();
    }

    if ( gifPlayersInPost.includes( 'canvas' ) ) {
        WP_GP_PP_initGIFCanvas();
    }

    if ( gifPlayersInPost.includes( 'video' ) ) {
        WP_GP_PP_toggleVideosGIF();
    }
}

// Register handlers when DOM is ready.
document.addEventListener( 'DOMContentLoaded', WP_GP_PP_INIT );
