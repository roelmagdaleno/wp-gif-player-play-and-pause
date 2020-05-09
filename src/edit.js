import {
    PanelBody,
    PanelRow,
    SelectControl,
    withNotices,
    Notice
} from '@wordpress/components';

import {
    Fragment
} from '@wordpress/element';

import {
    MediaPlaceholder,
    InspectorControls,
    BlockControls,
    BlockAlignmentToolbar,
    MediaReplaceFlow,
    __experimentalImageSizeControl as ImageSizeControl
} from '@wordpress/block-editor';

import ServerSideRender from '@wordpress/server-side-render';

const edit = ( { attributes, setAttributes, noticeOperations, noticeUI, clientId, isSelected } ) => {
    const {
        mediaID,
        mediaURL,
        gifMethod,
        width,
        height,
        imageWidth,
        imageHeight,
        align,
        useFallback
    } = attributes;

    function usingFallback() {
        if ( useFallback ) {
            return true;
        }

        const container = document.getElementById( `block-${ clientId }` );

        if ( ! container ) {
            return false;
        }

        const originalGif = container.querySelector( '.wp-gp-pp-gif' );

        if ( ! originalGif ) {
            return false;
        }

        return 'video' === gifMethod;
    }

    const onSelectImage = ( media ) => {
        let gifImage = {
            mediaID: media.id,
            mediaURL: media.url,
            width: media.width,
            height: media.height,
            imageWidth: media.width,
            imageHeight: media.height,
            gifMethod: gifMethod,
            useFallback: false
        };

        if ( media.wp_gp_pp_video_needs_fallback ) {
            gifImage.useFallback = media.wp_gp_pp_video_needs_fallback;
        }

        setAttributes( gifImage );
    };

    const onChangeGifMethod = ( gifMethod ) => {
        let attributes = {
            gifMethod
        };

        if ( useFallback && 'video' !== gifMethod ) {
            attributes.useFallback = false;
        }

        setAttributes( attributes )
    };

    const onImageError = ( message ) => {
        noticeOperations.removeAllNotices();
        noticeOperations.createErrorNotice( message );
    };

    const accept       = 'image/gif';
    const allowedTypes = ['image/gif'];

    if ( isSelected && ! useFallback ) {
        setAttributes( { useFallback: usingFallback() } );
    }

    return (
        <>
            <Fragment>
                <InspectorControls>
                    <PanelBody>
                        { useFallback && (
                            <PanelRow>
                                <Notice
                                    status = 'warning'
                                    isDismissible = { false }
                                    className = { 'wp-gp-pp__fallback-notice' }
                                >
                                    <p>This GIF Player was rendered as default GIF Player (GIF) because <strong>there are no valid video sources found</strong>.</p>
                                </Notice>
                            </PanelRow>
                        ) }

                        <PanelRow>
                            <SelectControl
                                label = { 'GIF Method' }
                                value = { gifMethod }
                                options = { [
                                    { label: 'GIF', value: 'gif' },
                                    { label: 'Canvas', value: 'canvas' },
                                    { label: 'Video', value: 'video', disabled: ! ( !! WP_GIF_PLAYER.ffmpegInstalled ) }
                                ] }
                                onChange = { onChangeGifMethod }
                            />
                        </PanelRow>

                        <PanelRow>
                            <ImageSizeControl
                                width = { width }
                                height = { height }
                                imageWidth = { imageWidth }
                                imageHeight = { imageHeight }
                                onChange = { ( newValue ) => { setAttributes( newValue ) } }
                            />
                        </PanelRow>
                    </PanelBody>
                </InspectorControls>

                <BlockControls>
                    <BlockAlignmentToolbar
                        value = { align }
                        controls = { ['left', 'center', 'right'] }
                        onChange = { ( align ) => setAttributes( { align } ) }
                    />
                    { mediaID && (
                        <MediaReplaceFlow
                            mediaID = { mediaID }
                            mediaURL = { mediaURL }
                            allowedTypes = { allowedTypes }
                            accept = { accept }
                            onSelect = { onSelectImage }
                        />
                    ) }
                </BlockControls>

                {
                    mediaID ?
                        <ServerSideRender
                            block = 'roelmagdaleno/gif-player'
                            attributes = { {
                                mediaID,
                                mediaURL,
                                gifMethod,
                                width,
                                height,
                                imageWidth,
                                imageHeight,
                                align
                            } }
                            className = { `align${align}` }
                        /> :
                        <MediaPlaceholder
                            accept = { accept }
                            allowedTypes = { allowedTypes }
                            icon = 'format-video'
                            value = { mediaID }
                            notices = { noticeUI }
                            labels = { {
                                title: 'Insert GIF Player',
                                instructions: 'The GIF you insert will render the GIF player method selected in the plugin settings page but you can change it using the block controls.'
                            } }
                            onSelect = { onSelectImage }
                            onError = { onImageError }
                        />
                }
            </Fragment>
        </>
    );
};

export default withNotices( edit );
