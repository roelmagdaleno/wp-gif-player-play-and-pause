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
function WP_GP_PP_toggleGif() {
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

// Register handlers when DOM is ready.
document.addEventListener( 'DOMContentLoaded', WP_GP_PP_toggleGif );
