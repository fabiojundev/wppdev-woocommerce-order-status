/**
 * Wrapper for validated component.
 * Show error message if present and touched.
 */

import React from 'react';

export const WoValidatedInput = (props) => {
    const {
        label,
        Component,
        touched,
        errors,
        handleChange,
        ...passThroughProps
    } = props;

    const { id, name, type = 'text' } = props;

    return (
        <>
            <label htmlFor={id || name}>{label}</label>
            <div>
                <Component
                    id={id ?? name}
                    type={type}
                    {...passThroughProps}
                    onChange={handleChange(name)}
                />
            </div>
            {touched[name] && errors[name] ? (
                <div className="error">{errors[name]}</div>
            ) : null}
        </>
    );
};