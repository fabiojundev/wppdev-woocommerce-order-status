/**
 * WP Media uploader wrapper.
 */

import { Button } from '@wordpress/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faMinusCircle } from '@fortawesome/free-solid-svg-icons';

import { __ } from '@wordpress/i18n';
import { useState } from "@wordpress/element";

const ALLOWED_MEDIA_TYPES = ['image'];

import _ from 'lodash'
export default function MediaUploader(props) {
    _.noConflict();
    let media_uploader;
    const {
        multiple = false,
        type = 'image',
        order = 'Desc',
        orderBy = '',
        title = __('Select or Upload Media', 'wppdev-woocommerce-order-status'),
        text = __('Select', 'wppdev-woocommerce-order-status'),
        attachmentField = 'attachments',
        attachments = { id: '', ur: '' },
        previewType = 'img',
        className = 'wo-media-uploader-wrap',
        buttonText = __('Open Uploader', 'wppdev-woocommerce-order-status'),
        onChange,
    } = props;
    const [newAttachment, setNewAttachment] = useState(attachments || { id: '', url: '' });

    const openUploader = (event) => {
        event.preventDefault()

        if (media_uploader) {
            media_uploader.open()
            return
        }

        const config = {
            title: title,
            library: {
                order: order,
                orderby: orderBy,
                type: type,
            },
            button: {
                text: text,
            },
            multiple: multiple,
        };

        media_uploader = wp.media(config);

        media_uploader.on("select", function () {
            const json = media_uploader.state().get("selection").first().toJSON();

            const attach = {
                id: json.id,
                url: json.url,
            };

            setNewAttachment(attach);
            onChange(attachmentField)(attach);
        });

        media_uploader.open()
    }

    const deleteAttachment = () => {
        setNewAttachment({});
        onChange(attachmentField)({});
    };

    return (
        <React.Fragment>
            <div className={className}>
                <Button
                    isSecondary
                    type='button'
                    onClick={openUploader}
                    className='wo-upload-button'
                >
                    {buttonText}
                </Button>
                <div className='wo-preview-wrap'>
                    {newAttachment.url &&
                        <FontAwesomeIcon
                            icon={faMinusCircle}
                            onClick={deleteAttachment}
                            color="red"
                        />
                    }
                    <input
                        type="hidden"
                        id={attachmentField}
                        name={attachmentField}
                        value={newAttachment}
                    />
                    {('img' == previewType) ?
                        <img src={newAttachment.url} />
                        :
                        <label>
                            {newAttachment.url}
                        </label>
                    }
                </div>
            </div>
        </React.Fragment>
    );
}
