/**
 * Get all GIFs and load them when the DOM is ready.
 *
 * The thumbnail image is used as the video poster and
 * the <video> element is controlled by the overlay click handlers.
 *
 * @since 0.1.0
 */
function WP_GP_PP_toggleGif() {
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

// Register handlers when DOM is ready.
document.addEventListener( 'DOMContentLoaded', WP_GP_PP_toggleGif );
