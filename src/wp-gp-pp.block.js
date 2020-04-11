import { registerBlockType } from '@wordpress/blocks';

import edit from './edit';

registerBlockType( 'roelmagdaleno/gif-player', {
    title: 'GIF Player',
    description: 'Attach GIF players into your posts and pages.',
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
        },
        align: {
            type: 'string',
            default: 'center'
        }
    },
    transforms: {
        from: [
            {
                type: 'shortcode',
                tag: 'gif-player',
                attributes: {
                    mediaID: {
                        type: 'number',
                        shortcode: function ( attributes ) {
                            return parseInt( attributes.named.id );
                        }
                    }
                }
            }
        ]
    },
    edit,
    save: () => null
} );
