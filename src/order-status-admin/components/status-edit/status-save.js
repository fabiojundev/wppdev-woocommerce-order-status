/**
 * Save order status component.
 * Show cancel and save button.
 * Show loading feedback and error messages.
 */

import React from 'react';
import {
    Button,
} from '@wordpress/components';
import { useState } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { __ } from '@wordpress/i18n';

import { STATUS_STORE_KEY } from '../../data';
import { WO_TEXT_DOMAIN } from '../../constants/index.js';
import ButtonLoader from '../button-loader';


export default function StatusSave(props) {
    const {
        status,
        schema,
        validate,
        onCancel,
    } = props;

    const { createStatus, updateStatus, clearErrorMsgs } = useDispatch(
        STATUS_STORE_KEY
    );

    const getReqName = () => {
        return status.id
            ? 'update-status-' + status.id
            : 'create-status';
    };

    const [reqName, setReqName] = useState(getReqName());
    
    const request = useSelect(
        select => select(STATUS_STORE_KEY).getRequest(reqName),
        [reqName]
    );

    const { inProgress, message } = request || { inProgress: false, message: '' };
    const [error, setError] = useState('');

    const handleSave = () => {

        setError('');
        if (schema) {
            schema.validate(validate, { abortEarly: false })
                .then(v => {
                    save();
                })
                .catch(err => {
                    const error = err.errors?.join(', ');
                    setError(error);
                });
        }
        else {
            save();
        }
    };

    const save = () => {
        if (status.id) {
            setReqName(getReqName())
            updateStatus(status);
        }
        else {
            setReqName(getReqName());
            createStatus(status);
        }
    };

    return (
        <div className="wo-row">
            <Button
                isSecondary
                onClick={onCancel}
                disabled={inProgress}
            >
                {__('Back', WO_TEXT_DOMAIN)}
            </Button>
            <ButtonLoader
                isPrimary
                onClick={handleSave}
                label={__('Save', WO_TEXT_DOMAIN)}
                loading={inProgress}
                message={message || error}
                clear={() => clearErrorMsgs(reqName)}
            />
        </div>
    );
}