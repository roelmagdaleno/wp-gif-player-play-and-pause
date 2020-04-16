import {
    PanelBody,
    PanelRow,
    SelectControl,
    withNotices
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

const edit = ( { attributes, setAttributes, noticeOperations, noticeUI } ) => {
    const {
        mediaID,
        mediaURL,
        gifMethod,
        width,
        height,
        imageWidth,
        imageHeight,
        align
    } = attributes;

    const onSelectImage = ( media ) => {
        setAttributes( {
            mediaID: media.id,
            mediaURL: media.url,
            width: media.width,
            height: media.height,
            imageWidth: media.width,
            imageHeight: media.height,
            gifMethod: gifMethod
        } );
    };

    const onImageError = ( message ) => {
        noticeOperations.removeAllNotices();
        noticeOperations.createErrorNotice( message );
    };

    const accept       = 'image/gif';
    const allowedTypes = ['image/gif'];

    return (
        <>
            <Fragment>
                <InspectorControls>
                    <PanelBody>
                        <PanelRow>
                            <SelectControl
                                label = { 'GIF Method' }
                                value = { gifMethod }
                                options = { [
                                    { label: 'GIF', value: 'gif' },
                                    { label: 'Canvas', value: 'canvas' },
                                    { label: 'Video', value: 'video', disabled: ! ( !! WP_GIF_PLAYER.ffmpegInstalled ) }
                                ] }
                                onChange = { ( gifMethod ) => setAttributes( { gifMethod } ) }
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
                            attributes = { attributes }
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
