/**
 * Order status email settings component.
 */

/**
 * External dependencies.
 */
import React from 'react';
import {
    TextControl,
    TextareaControl,
    ToggleControl,
    PanelBody
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from "@wordpress/element";
import * as Yup from 'yup';
import { setLocale } from 'yup';

/**
 * Internal dependencies.
 */
import { WoValidatedInput } from '../validated-input';
import StatusSave from './status-save';
import DefaultStatus from './status';
import TriggerConditions from './trigger-conditions';
import MediaUpload from '../media-upload';

export default function EmailSettings(props) {

    const { status, orderStatuses, onCancel } = props;
    const [values, setValues] = useState({
        ...DefaultStatus.email_settings,
        ...status.email_settings,
    });
    const {
        enabled,
        recipients,
        subject,
        message,
        include_order,
        attachments,
        conditions,
    } = values;

    const [errors, setErrors] = React.useState({});
    const [touched, setTouched] = React.useState({});

    const handleChange = property => value => {
        const val = value.target ? value.target?.value : value;

        setValues({
            ...values,
            [property]: val
        });

        setTouched({
            ...touched,
            [property]: true,
        });
    };

    const schema = Yup.object({
        enabled: Yup.boolean(),
        recipients: Yup.mixed()
            .when('enabled', {
                is: true,
                then: Yup.array()
                    .transform(function (value, originalValue) {
                        if (this.isType(value) && value !== null) {
                            return value;
                        }
                        return originalValue ? originalValue.split(/[\s,]+/) : [];
                    })
                    .of(Yup.string()
                        .email(({ value }) => value + __('is not a valid email', 'wppdev-woocommerce-order-status'))
                    )
                    .required().min(1),
                otherwise: Yup.string(),
            }),
        subject: Yup.string()
            .when('enabled', {
                is: true,
                then: Yup.string().required()
                    .min(3)
                    .max(255)
            }),
    });

    const validate = (name, value) => {
        const { [name]: removedError, ...rest } = errors;

        schema.validateAt(name, { [name]: value, enabled: enabled })
            .then(v => {
                setErrors({
                    ...rest
                });
            }
            ).catch(err => {
                const error = err.errors?.join(',');
                setErrors({
                    ...rest,
                    ...(error && { [name]: touched[name] && error }),
                });
                // handleErrors(name, error);
            });
    };

    const handleBlur = evt => {
        const { name, value } = evt?.target;
        validate(name, value);
    };

    return (
        <PanelBody className="wo-tab-email-settings">
            <h2>{__('Send Notification Email', 'wppdev-woocommerce-order-status')}</h2>
            <hr />
            <ToggleControl
                id="enabled"
                name="enabled"
                label={__('Enabled', 'wppdev-woocommerce-order-status')}
                checked={enabled}
                onChange={handleChange('enabled')}
            />
            {enabled && (
                <React.Fragment>
                    <WoValidatedInput
                        Component={TextControl}
                        name="recipients"
                        label={__('Email Recipients (use ", " to separe)', 'wppdev-woocommerce-order-status')}
                        value={recipients}
                        onBlur={handleBlur}
                        handleChange={handleChange}
                        errors={errors}
                        touched={touched}
                    />
                    <WoValidatedInput
                        Component={TextControl}
                        name="subject"
                        label={__('Subject', 'wppdev-woocommerce-order-status')}
                        value={subject}
                        onBlur={handleBlur}
                        handleChange={handleChange}
                        errors={errors}
                        touched={touched}
                    />
                    <TextareaControl
                        id="message"
                        name="message"
                        label={__('Message', 'wppdev-woocommerce-order-status')}
                        value={message}
                        onChange={handleChange('message')}
                    />
                    <MediaUpload
                        attachmentField="attachments"
                        attachments={attachments}
                        previewType="img"
                        title={__('Add E-mail Attachments', 'wppdev-woocommerce-order-status')}
                        onChange={handleChange}
                        buttonText={__('Add attachments', 'wppdev-woocommerce-order-status')}
                    />
                    <ToggleControl
                        id="include_order"
                        name="include_order"
                        label={__('Include Order details', 'wppdev-woocommerce-order-status')}
                        checked={include_order}
                        onChange={handleChange('include_order')}
                    />
                    <br />
                    <TriggerConditions
                        conditions={conditions}
                        orderStatuses={orderStatuses}
                        onChange={handleChange}
                        title={__('Conditions to Send', 'wppdev-woocommerce-order-status')}
                        status={status}
                    />
                </React.Fragment>
            )}
            <hr />
            <StatusSave
                status={{ ...status, email_settings: values }}
                validate={values}
                onCancel={onCancel}
                schema={schema}
            />
        </PanelBody>
    );
}
