/**
 * Trigger conditions component.
 */

import React from 'react';
import {
    ToggleControl,
} from '@wordpress/components';
import { useState } from "@wordpress/element";
import { __ } from '@wordpress/i18n';

import { WO_TEXT_DOMAIN } from '../../constants/index.js';
import { ReactSelect, onSelect } from '../react-select';

const TriggerConditions = (props) => {
    const {
        title,
        conditions = {},
        status,
        orderStatuses,
        onChange: handleChange,
    } = props;

    const [updatedConditions, setUpdatedConditions] = useState(conditions);

    const labels = {
        enabled: __('Enable Condition', WO_TEXT_DOMAIN),
        always: __('Select below', WO_TEXT_DOMAIN),
        if_overdue: __('If overdue time estimative - ', WO_TEXT_DOMAIN)
                        + ( status?.days_estimation ? status.days_estimation 
                        + __(' days', WO_TEXT_DOMAIN) : '' ),
        from_statuses: __('If Changed From Status', WO_TEXT_DOMAIN),
    };

    const getDesc = ({if_overdue, from_statuses}) => {
        let res = [];
        if( if_overdue ) {
            res = [ labels.if_overdue ];
        }
        if( from_statuses?.length ) {
            const from = from_statuses.map( id => {
                return orderStatuses.find( st => st.value == id )?.label;
            })
            .join( __(' OR ', WO_TEXT_DOMAIN) );

            res = [
                ...res,
                `${labels.from_statuses}: ( ${from} )`
            ];
        }
        if( res.length > 1 ) {
            res = res.join( __(' AND ', WO_TEXT_DOMAIN) )
        }
        else {
            res = res.join('');
        }
        if( ! res ) {
            res = labels.always;
        }
        return res;
    };

    const {
        enabled = false,
        if_overdue = false,
        from_statuses = [],
        desc = getDesc(updatedConditions),
    } = updatedConditions;


    const onChange = property => value => {
        const val = value.value ? value.value : value;
        let newConditions = {
            ...updatedConditions,
            [property]: val,
        };
        newConditions = {
            ...newConditions,
            desc: getDesc(newConditions),
        };

        setUpdatedConditions(newConditions);
        handleChange('conditions')(newConditions);
    };

    let enabledDesc = labels.enabled;
    if( enabled && desc ) {
        enabledDesc = `${enabledDesc}: ${desc}`;
    }

    return (
        <div className="wo-status-conditions">
            <h2>{title ? title : __('Conditions', WO_TEXT_DOMAIN)}</h2>
            <ToggleControl
                id="enabled"
                name="enabled"
                label={enabledDesc}
                checked={enabled}
                onChange={onChange('enabled')}
            />
            {enabled && (
                <>
                    <hr />
                    {/* <ToggleControl
                        id="if_overdue"
                        name="if_overdue"
                        label={labels.if_overdue}
                        checked={if_overdue}
                        onChange={onChange('if_overdue')}
                    /> */}
                    <label>{__('If Changed From Status', WO_TEXT_DOMAIN)}</label>
                    <ReactSelect
                        isMulti
                        id="from_statuses"
                        name="from_statuses"
                        label={labels.from_statuses}
                        value={from_statuses}
                        options={orderStatuses}
                        onChange={onSelect('from_statuses', onChange)}
                    />
                </>
            )}
        </div>
    )
};

export default TriggerConditions;