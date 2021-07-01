/**
 * Order status main settings edit component.
 */

import React from 'react';
import { useState } from "@wordpress/element";
import {
    TextControl,
    TextareaControl,
    ToggleControl,
    PanelBody,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import * as Yup from 'yup';
import { setLocale } from 'yup';

import { ReactSelect, onSelect } from '../react-select';
import { TYPE_CORE, TYPE_CUSTOM } from "../../constants";
import { IconPicker, IconPickerItem } from 'react-fa-icon-picker';
import { ColorIndicatorPicker } from '../color-picker';
import { WoValidatedInput } from '../validated-input';
import StatusSave from './status-save';
import { chooseFontColor } from '../color-picker';
import DefaultStatus from './status';

export default function StatusSettings(props) {
    const { status, orderStatuses, onCancel } = props;

    const [values, setValues] = useState({
        ...DefaultStatus,
        ...status
    });

    const {
        name,
        slug,
        type,
        description,
        days_estimation,
        color,
        background,
        icon,
        enabled_in_bulk_actions,
        enabled_in_reports,
        next_statuses
    } = values;

    const disabled = type == TYPE_CORE ? true : false;

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

    const handleColorChange = (colorProp, backgroundProp) => value => {
        const bkColor = value.hex;
        const color = chooseFontColor(bkColor);

        setValues({
            ...values,
            [backgroundProp]: bkColor,
            [colorProp]: color,
        });

        setTouched({
            ...touched,
            [backgroundProp]: true,
            [colorProp]: true,
        });
    };

    const [errors, setErrors] = React.useState({});
    const [touched, setTouched] = React.useState({});

    const schema = Yup.object({
        name: Yup.string()
            .min(3)
            .max(100)
            .required(),
        slug: Yup.string()
            .min(3)
            .max(20)
            .required(),
        days_estimation: Yup.number()
            .required()
            .integer(),
    });

    const validate = (name, value) => {
        const { [name]: removedError, ...rest } = errors;

        schema.validateAt(name, { [name]: value })
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
            });
    };

    const handleBlur = evt => {
        const { name, value } = evt?.target;
        validate(name, value);
    };

    return (
        <PanelBody className="wo-tab-settings">
            <h2>{__('Status Settings', 'wppdev-woocommerce-order-status')}</h2>
            <hr />
            <div className={"wo-settings-wrap"}>
                <WoValidatedInput
                    name="name"
                    label={__('Name', 'wppdev-woocommerce-order-status')}
                    Component={TextControl}
                    value={name}
                    onBlur={handleBlur}
                    handleChange={handleChange}
                    errors={errors}
                    touched={touched}
                />
                <WoValidatedInput
                    name="slug"
                    label={__('Slug (without prefix wc-)', 'wppdev-woocommerce-order-status')}
                    Component={TextControl}
                    value={slug}
                    onBlur={handleBlur}
                    handleChange={handleChange}
                    errors={errors}
                    touched={touched}
                    disabled={disabled}
                />
                <TextareaControl
                    name="description"
                    label={__('Description', 'wppdev-woocommerce-order-status')}
                    value={description}
                    onChange={handleChange('description')}
                />
                {/* <WoValidatedInput
                    name="days_estimation"
                    label={__('Time estimative in this Status (days)', 'wppdev-woocommerce-order-status')}
                    Component={TextControl}
                    type={"number"}
                    value={days_estimation}
                    onBlur={handleBlur}
                    handleChange={handleChange}
                    errors={errors}
                    touched={touched}
                    step="1"
                    min="0"
                /> */}
                <div className={"wo-row"}>
                    <div className={"wo-col"}>
                        <ColorIndicatorPicker
                            label={__('Color', 'wppdev-woocommerce-order-status')}
                            color={background}
                            className={"wo-status-color-picker"}
                            handleColorChange={handleColorChange}
                            colorProp={'color'}
                            backgroundProp={'background'}
                            disabled={disabled}
                        />
                    </div>
                    <div className={"wo-col"}>
                        <label>{__('Icon', 'wppdev-woocommerce-order-status')}</label>
                        {disabled ? 
                            <IconPickerItem 
                                icon={icon}
                                size={20}
                            />
                        : <IconPicker
                            name="icon"
                            onChange={value => !disabled ? handleChange('icon')(value) : null}
                            value={icon}
                            size={20}
                            containerStyles={{ background: '#efefef' }}
                            />                        
                        }
                    </div>
                </div>
                <ToggleControl
                    name="enabled_in_bulk_actions"
                    label={__('Enable in Orders Bulk Actions', 'wppdev-woocommerce-order-status')}
                    checked={enabled_in_bulk_actions}
                    onChange={handleChange('enabled_in_bulk_actions')}
                    disabled={disabled}
                />
                <ToggleControl
                    name="enabled_in_reports"
                    label={__('Enable in Reports', 'wppdev-woocommerce-order-status')}
                    checked={enabled_in_reports}
                    onChange={handleChange('enabled_in_reports')}
                    disabled={disabled}
                />
                <h2>{__('Next Statuses to show in Order Actions', 'wppdev-woocommerce-order-status')}</h2>
                <ReactSelect
                    isMulti
                    id="next_statuses"
                    name="next_statuses"
                    label={__('Next Statuses in Actions', 'wppdev-woocommerce-order-status')}
                    value={next_statuses}
                    options={orderStatuses}
                    onChange={onSelect('next_statuses', handleChange)}
                />
                <hr />
                <StatusSave
                    status={values}
                    validate={values}
                    onCancel={onCancel}
                    schema={schema}
                />
            </div>
        </PanelBody>
    );
}
