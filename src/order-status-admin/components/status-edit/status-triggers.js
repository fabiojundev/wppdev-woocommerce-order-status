import React from 'react';
import {
    Button,
    PanelBody
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from "@wordpress/element";

import TriggerSettings from './status-trigger-settings';
import StatusSave from './status-save';
import uniqid from "uniqid";

export default function StatusTriggers(props) {

    const { status, orderStatuses, onCancel } = props;
    const [values, setValues] = useState(status.trigger_settings || [] );

    const addTrigger = (val) => {
        const newTrigger = [
            ...values,
            {
                id: uniqid(),
                trigger_type: null,
                trigger_value: null,
                from_statuses: [],
                if_overdue: false,
            }
        ];

        setValues(newTrigger);
    };

    const handleChange = (index, value) => {
        const triggers = values.map( (trigger, i ) => {
            if( i == index ) {
                return value;
            }
            else {
                return trigger;
            }
        });

        setValues(triggers);
    };
    
    const onDelete = index => {
        setValues(values.filter( (val, i) => i != index ) );
    };

    return (
        <PanelBody className="wo-tab-trigger-settings">
            <Button
                isSecondary
                onClick={addTrigger}
            >
                {__('Add Automation', 'wppdev-woocommerce-order-status')}
            </Button>
            {values && values.map( function(trigger, index) {
                console.log( index, trigger);
                if( trigger ) {
                    return(
                        <TriggerSettings
                            key={index}
                            id={index}
                            trigger={trigger} 
                            orderStatuses={orderStatuses}
                            handleChange={handleChange}
                            handleDelete={onDelete}
                            status={status}
                        />
                    );
                }
            }
            )}
            <StatusSave
                status={{ ...status, trigger_settings: values }}
                validate={values}
                onCancel={onCancel}
            />
        </PanelBody>
    );
};
