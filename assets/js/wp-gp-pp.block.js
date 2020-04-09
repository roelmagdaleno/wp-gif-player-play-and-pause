( function ( blocks, blockEditor, element, components, serverSideRender ) {
    const el = element.createElement;

    const registerBlockType = blocks.registerBlockType;
    const ServerSideRender  = serverSideRender;
    const Fragment          = element.Fragment;
    const MediaPlaceholder  = blockEditor.MediaPlaceholder;
    const InspectorControls = blockEditor.InspectorControls;
    const BlockControls     = blockEditor.BlockControls;
    const ImageSizeControl  = blockEditor.__experimentalImageSizeControl;
    const MediaReplaceFlow  = blockEditor.MediaReplaceFlow;
    const PanelBody         = components.PanelBody;
    const PanelRow          = components.PanelRow;
    const SelectControl     = components.SelectControl;

    registerBlockType( 'roelmagdaleno/gif-player', {
        title: 'GIF Player',
        description: 'Attach a GIF player into your pages and posts.',
        category: 'common',
        icon: 'format-video',
        keywords: [ 'gif', 'player' ],
        attributes: {
            mediaID: {
                type: 'number'
            },
            mediaURL: {
                type: 'string'
            },
            gifMethod: {
                type: 'string',
                default: WP_GIF_PLAYER.gifMethod
            },
            width: {
                type: 'number'
            },
            height: {
                type: 'number'
            },
            imageWidth: {
                type: 'number'
            },
            imageHeight: {
                type: 'number'
            }
        },
        edit: function ( props ) {
            let attributes    = props.attributes;
            let setAttributes = props.setAttributes;
            let mediaID       = attributes.mediaID;
            let mediaURL      = attributes.mediaURL;
            let gifMethod     = attributes.gifMethod;
            let width         = attributes.width;
            let height        = attributes.height;
            let imageWidth    = attributes.imageWidth;
            let imageHeight   = attributes.imageHeight;

            const allowedTypes = 'image/gif';
            const acceptImage  = 'image/gif';

            function onSelectImage( media ) {
                setAttributes( {
                    mediaID: media.id,
                    mediaURL: media.url,
                    width: media.width,
                    height: media.height,
                    imageWidth: media.width,
                    imageHeight: media.height,
                    gifMethod: gifMethod
                } );
            }

            function onChangeGifMethod( newGifMethod ) {
                setAttributes( { gifMethod: newGifMethod } );
            }

            function onChangeImageSize( newValue ) {
                setAttributes( newValue );
            }

            function getInspectorControls() {
                return el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        null,
                        el(
                            PanelRow,
                            null,
                            el(
                                SelectControl,
                                {
                                    label: 'GIF Method',
                                    value: gifMethod,
                                    options: [
                                        { label: 'GIF', value: 'gif' },
                                        { label: 'Canvas', value: 'canvas' },
                                        { label: 'Video', value: 'video' }
                                    ],
                                    onChange: onChangeGifMethod
                                }
                            )
                        ),
                        el(
                            PanelRow,
                            null,
                            el(
                                ImageSizeControl,
                                {
                                    width: width,
                                    height: height,
                                    imageWidth: imageWidth,
                                    imageHeight: imageHeight,
                                    onChange: onChangeImageSize
                                }
                            )
                        )
                    )
                );
            }

            function getBlockControls() {
                return el(
                    BlockControls,
                    null,
                    mediaID && el(
                        MediaReplaceFlow, {
                        mediaID: mediaID,
                        mediaURL: mediaURL,
                        allowedTypes: allowedTypes,
                        accept: acceptImage,
                        onSelect: onSelectImage
                    } )
                );
            }

            function getGifPlayer() {
                return mediaID
                    ? el( ServerSideRender, {
                        block: 'roelmagdaleno/gif-player',
                        attributes: attributes
                    } )
                    : el( MediaPlaceholder, {
                        accept: acceptImage,
                        allowedTypes: allowedTypes,
                        icon: 'format-video',
                        value: {
                            mediaID,
                            gifMethod
                        },
                        labels: {
                            title: 'Insert GIF Player',
                            instructions: 'The GIF you insert will render the GIF player method selected in the plugin settings page but you can change it using the block controls.'
                        },
                        onSelect: onSelectImage
                    } );
            }

            return (
                el(
                    Fragment,
                    null,
                    getInspectorControls(),
                    getBlockControls(),
                    getGifPlayer()
                )
            );
        },
        save: function () {
            return null;
        }
    } );
} )(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.element,
    window.wp.components,
    window.wp.serverSideRender
);
