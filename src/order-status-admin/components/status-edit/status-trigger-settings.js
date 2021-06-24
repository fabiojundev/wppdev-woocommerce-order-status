/**
 * Order status trigger settings component.
 */

import React from 'react';
import {
    Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from "@wordpress/element";

import { WPPDEV_WO_TXT_DM } from '../../constants/index.js';
import { ReactSelect, onSelect } from '../react-select';
import TriggerConditions from './trigger-conditions';

const TRIGGER_TYPE_CHANGE_STATUS = 'trigger_change_status';
const TRIGGER_TYPE_RESEND_INVOICE = 'trigger_resend_invoice';
const triggerTypes = [
    {
        'label': __('Select', WPPDEV_WO_TXT_DM),
        'value': '',
    },
    {
        'label': __('Change Status', WPPDEV_WO_TXT_DM),
        'value': TRIGGER_TYPE_CHANGE_STATUS,
    },
    {
        'label': __('Resend Invoice', WPPDEV_WO_TXT_DM),
        'value': TRIGGER_TYPE_RESEND_INVOICE,
    },
];

const TriggerSettings = (props) => {
    const {
        id,
        trigger,
        status,
        orderStatuses,
        handleChange,
        handleDelete
    } = props;
    const [updatedTrigger, setUpdatedTrigger] = useState(trigger);
    const {
        trigger_type = '',
        to_status = '',
        to_emails = [],
        cc_emails = [],
        conditions = {},
    } = updatedTrigger;

    const onChange = property => value => {
        const val = value.value ? value.value : value;
        const trigger = {
            ...updatedTrigger,
            [property]: val
        };

        setUpdatedTrigger(trigger);
        handleChange(id, trigger);
    };

    const onTriggerTypeChange = value => {
        const property = 'trigger_type';
        const val = value.value ? value.value : value;

        onSelect(property, onChange)(val);
        getTriggerView(val);
    };

    const onDelete = () => {
        handleDelete(id);
    };

    const getTriggerView = (type) => {
        switch (type) {
            case TRIGGER_TYPE_CHANGE_STATUS:
                return (
                    <TriggerChangeStatus />
                );
            case TRIGGER_TYPE_RESEND_INVOICE:
                return (
                    <TriggerResendInvoice />
                );
            default:
                return;
        }
    };

    const TriggerChangeStatus = () => {

        return (
            <div className="wo-trigger-change-status">
                <label>{__('To Order Status', WPPDEV_WO_TXT_DM)}</label>
                <br />
                <ReactSelect
                    id="to_status"
                    name="to_status"
                    label={__('To Status', WPPDEV_WO_TXT_DM)}
                    value={to_status}
                    options={orderStatuses}
                    onChange={onSelect('to_status', onChange)}
                />
                <br />
                <TriggerConditions
                    conditions={conditions}
                    orderStatuses={orderStatuses}
                    onChange={onChange}
                    status={status}
                />
            </div>
        )
    };

    const TriggerResendInvoice = () => {
        const options = [
            { label: __('Client', WPPDEV_WO_TXT_DM), value: 'client' },
            { label: __('Admin', WPPDEV_WO_TXT_DM), value: 'admin' },
            { label: __('Admin and Client', WPPDEV_WO_TXT_DM), value: 'both' },
        ];
        return (
            <div className="wo-trigger-resend-invoice">
                <label>{__('Resend Invoice To', WPPDEV_WO_TXT_DM)}</label>
                <br />
                <ReactSelect
                    id="to_emails"
                    name="to_emails"
                    label={__('To Status', WPPDEV_WO_TXT_DM)}
                    options={options}
                    value={to_emails}
                    onChange={onSelect('to_emails', onChange)}
                />
                {/* <TextControl
                    id="cc_emails"
                    name="cc_emails"
                    label={__('Send an email copy to', WPPDEV_WO_TXT_DM)}
                    value={cc_emails}
                    onChange={onChange('cc_emails')}
                /> */}
                <br />
                <TriggerConditions
                    conditions={conditions}
                    orderStatuses={orderStatuses}
                    onChange={onChange}
                    status={status}
                />
            </div>
        )
    };

    return (
        <div className="wo-trigger-settings">
            <div
                className="wo-trigger-header"
            >
                {__('Automation', WPPDEV_WO_TXT_DM) + " - " + id}
                <Button
                    isDestructive
                    onClick={onDelete}
                >
                    {__('Delete', WPPDEV_WO_TXT_DM)}
                </Button>
            </div>
            <div className="wo-trigger-wrap">
                <ReactSelect
                    id="trigger_type"
                    name="trigger_type"
                    label={__('Execute Action', WPPDEV_WO_TXT_DM)}
                    value={trigger_type ? trigger_type : ''}
                    options={triggerTypes}
                    onChange={onTriggerTypeChange}
                />
                {getTriggerView(trigger_type)}
            </div>
        </div>
    );
}

export default TriggerSettings;
