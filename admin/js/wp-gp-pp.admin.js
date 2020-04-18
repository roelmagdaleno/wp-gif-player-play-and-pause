/**
 * Run the testing ffmpeg command using AJAX.
 *
 * When the AJAX request is done we will set the error
 * or success notice with its title and description.
 *
 * @since 0.1.0
 *
 * @param {object}   buttonEl   The clicked button to test ffmpeg.
 */
function WP_GP_PP_testFFmpeg( buttonEl ) {
    buttonEl.disabled  = true;
    buttonEl.innerHTML = 'Testing FFmpeg...';

    const xhr = new XMLHttpRequest();

    xhr.open( 'POST', WP_GP_PP_ADMIN.admin_url, true );
    xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
    xhr.onload = () => {
        if ( xhr.status >= 200 && xhr.status < 400 ) {
            const response      = JSON.parse( xhr.response );
            const noticeEl      = document.querySelector( '#wp-gp-pp-admin-notice' );
            const titleEl       = document.querySelector( '.wp-gp-pp-title' );
            const descriptionEl = document.querySelector( '.wp-gp-pp-description' );

            titleEl.innerHTML       = `<strong>${ response.data.title }</strong>`;
            descriptionEl.innerHTML = response.data.description;

            if ( response.success ) {
                noticeEl.classList.remove( 'notice-warning' );
                noticeEl.classList.add( 'notice-success' );

                document.querySelector( '#video' ).disabled = false;
                document.querySelector( '.wp-gp-pp-button-section' ).remove();

                return;
            }

            buttonEl.disabled  = false;
            buttonEl.innerHTML = 'Test FFmpeg';
        }
    };

    xhr.send( `action=wp_gp_pp_test_ffmpeg&_wpnonce=${WP_GP_PP_ADMIN.ajax_nonce}` );
}
