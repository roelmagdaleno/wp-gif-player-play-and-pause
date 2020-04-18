/**
 * Set a handler click to our "Add GIF" button.
 *
 * This button will open the Media Library so the user
 * can select or upload a new GIF.
 *
 * @since 0.1.0
 */
function WP_GP_PP_initMediaUploader() {
    const uploaderButton = document.getElementById( 'wp-gp-pp-media-uploader' );

    if ( ! uploaderButton ) {
        return;
    }

    uploaderButton.onclick = WP_GP_PP_openMediaUploader;
}

/**
 * Open the Media Library frame when user clicks
 * in our "Add GIF" button.
 *
 * Also set a "select" listener so the selected GIF(s)
 * can be inserted as a formatted shortcode.
 *
 * @since 0.1.0
 *
 * @param {object}   e   The current JavaScript event.
 */
function WP_GP_PP_openMediaUploader( e ) {
    e.preventDefault();

    const mediaOptions = {
        title: 'Insert GIF(s)',
        library: { type: 'image/gif' },
        multiple: true,
        button: { text: 'Insert GIF(s)' }
    };

    const mediaFrame = wp.media( mediaOptions );

    mediaFrame.on( 'select', () => {
        const gifs = mediaFrame.state().get( 'selection' ).toJSON();

        for ( let i = 0; i < gifs.length; i++ ) {
            if ( 'image/gif' !== gifs[ i ].mime ) {
                continue;
            }

            wp.media.editor.insert( `[gif-player id="${ gifs[ i ].id }"]` );
        }
    } );

    mediaFrame.open();
}

// Register handlers when DOM is ready.
document.addEventListener( 'DOMContentLoaded', WP_GP_PP_initMediaUploader );
