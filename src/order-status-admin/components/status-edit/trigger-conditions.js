/**
 * Trigger conditions component.
 */

import React from 'react';
import {
    ToggleControl,
} from '@wordpress/components';
import { useState } from "@wordpress/element";
import { __ } from '@wordpress/i18n';

import { WPPDEV_WO_TXT_DM } from '../../constants/index.js';
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
        enabled: __('Enable Condition', WPPDEV_WO_TXT_DM),
        always: __('Select below', WPPDEV_WO_TXT_DM),
        if_overdue: __('If overdue time estimative - ', WPPDEV_WO_TXT_DM)
                        + ( status?.days_estimation ? status.days_estimation 
                        + __(' days', WPPDEV_WO_TXT_DM) : '' ),
        from_statuses: __('If Changed From Status', WPPDEV_WO_TXT_DM),
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
            .join( __(' OR ', WPPDEV_WO_TXT_DM) );

            res = [
                ...res,
                `${labels.from_statuses}: ( ${from} )`
            ];
        }
        if( res.length > 1 ) {
            res = res.join( __(' AND ', WPPDEV_WO_TXT_DM) )
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
            <h2>{title ? title : __('Conditions', WPPDEV_WO_TXT_DM)}</h2>
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
                    <label>{__('If Changed From Status', WPPDEV_WO_TXT_DM)}</label>
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