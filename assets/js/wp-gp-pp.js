const pauseObserver = new IntersectionObserver( WP_GP_PP_maybePauseGIF, {
    rootMargin: '100px 0px 100px 0px',
    threshold: 0.7
} );

const WP_GP_PP_OVERLAY_SELECTOR = '.wp-gp-pp-overlay';

/**
 * Pause the GIF when the player is out of the container view.
 * We're using the IntersectionObserver API to achieve that.
 *
 * @since 0.1.0
 *
 * @param {array}                  entries    The current observed elements.
 * @param {IntersectionObserver}   observer   The current observer object.
 */
function WP_GP_PP_maybePauseGIF( entries, observer ) {
    for ( let i = 0; i < entries.length; i++ ) {
        const entry = entries[i];

        if ( ! entry.isIntersecting ) {
            continue;
        }

        const gifEl = entry.target;

        if ( ! gifEl.dataset.playing ) {
            continue;
        }

        const overlay = gifEl.querySelector( WP_GP_PP_OVERLAY_SELECTOR );

        if ( ! overlay ) {
            continue;
        }

        overlay.click();
    }
}

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
        const gif       = gifs[i];
        const container = gif.parentElement;
        const overlay   = container.querySelector( WP_GP_PP_OVERLAY_SELECTOR );
        const dataset   = container.dataset;
        const superGif  = new SuperGif( {
            gif: gif,
            c_w: dataset.width,
            c_h: dataset.height,
            max_width: dataset.width
        } );

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

    const container = overlay.parentElement;
    pauseObserver.observe( container );

    overlay.onclick = () => {
        const button    = overlay.children[0];
        const isPlaying = superGif.get_playing();

        isPlaying ? delete container.dataset.playing : container.dataset.playing = 'true';
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
        const video     = videos[ i ];
        const overlay   = video.parentElement.querySelector( WP_GP_PP_OVERLAY_SELECTOR );
        const container = video.parentElement;

        overlay.onclick = () => {
            const button    = overlay.children[0];
            const classList = button.classList;
            const isPlaying = classList.contains( 'is-playing' );

            isPlaying ? delete container.dataset.playing : container.dataset.playing = 'true';
            isPlaying ? classList.remove( 'is-playing' ) : classList.add( 'is-playing' );
            isPlaying ? video.pause() : video.play();
        };

        pauseObserver.observe( container );
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
        const realGifEl = container.querySelector( '.wp-gp-pp-gif' );
        const overlay   = container.querySelector( WP_GP_PP_OVERLAY_SELECTOR );

        pauseObserver.observe( container );

        overlay.onclick = () => {
            const button    = overlay.children[0];
            const classList = button.classList;
            const isPlaying = classList.contains( 'is-playing' );

            isPlaying ? classList.remove( 'is-playing' ) : classList.add( 'is-playing' );

            let thumbnailImage = thumbnail.getAttribute( 'src' );
            let gifImage       = thumbnailImage.replace( '_gif_thumbnail.jpeg', '.gif' );

            if ( ! WP_GP_PP_realGIFhasSource( realGifEl, gifImage ) ) {
                realGifEl.setAttribute( 'src', gifImage );
            }

            isPlaying ? delete container.dataset.playing : container.dataset.playing = 'true';
            isPlaying ? container.classList.remove( 'is-playing' ) : container.classList.add( 'is-playing' );
        };
    }
}

/**
 * Check if the real GIF has the GIF source in its attribute.
 *
 * Some lazy load plugin might add a base64 pixel so we have to
 * read that and decide to show the real GIF or not.
 *
 * @since  0.1.1
 *
 * @param  {Element}   realGifEl   The original GIF element.
 * @param  {string}    gifImage    This is the thumbnail image source with replaced string.
 * @return {boolean}               Whether the original GIF has its source or not.
 */
function WP_GP_PP_realGIFhasSource( realGifEl, gifImage ) {
    const gifSrc = realGifEl.getAttribute( 'src' );

    if ( gifImage === gifSrc ) {
        return true;
    }

    if ( '' === gifSrc ) {
        return false;
    }

    const gifDataset = realGifEl.dataset;

    if ( gifDataset.hasOwnProperty( 'src' ) && '' === gifDataset.src ) {
        return false;
    }

    return ! ( gifSrc.includes( 'data' ) || gifSrc.includes( 'base64' ) || gifSrc.includes( 'lazy' ) );
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

    if ( gifPlayersInPost.includes( 'gif' ) || document.querySelector( '.wp-gp-pp-gif' ) ) {
        WP_GP_PP_toggleGIF();
    }

    if ( gifPlayersInPost.includes( 'canvas' ) || document.querySelector( '.wp-gp-pp-gif-canvas-player' ) ) {
        WP_GP_PP_initGIFCanvas();
    }

    if ( gifPlayersInPost.includes( 'video' ) || document.querySelector( '.wp-gp-pp-video-player' ) ) {
        WP_GP_PP_toggleVideosGIF();
    }
}

// Register handlers when DOM is ready.
document.addEventListener( 'DOMContentLoaded', WP_GP_PP_INIT );
