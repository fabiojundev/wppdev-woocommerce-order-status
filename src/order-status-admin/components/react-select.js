/**
 * Wrapper for react-select component.
 * Change default to return escalar value instead of array.
 */

import Select from 'react-select';

export const ReactSelect = (props) => {
    const { options = [], isMulti = false } = props;

    let selectOptions = {
        ...props,
        value: options.filter(({ value }) => value === props.value),
        getOptionLabel: ({ label }) => label,
        getOptionValue: ({ value }) => value,
    };
    if (isMulti) {
        selectOptions = {
            ...props,
            value: options.filter(({ value }) => {
                if (props.value && Array.isArray(props.value)) {
                    return props.value.some((state_val) => state_val === value);
                }
            }),
            getOptionLabel: ({ label }) => label,
            getOptionValue: ({ value }) => value,
        };
    }
    return (
        <Select
            {...selectOptions}
        />
    );
};

export const onSelect = (property, onChange) => value => {
    let val = value.value ? value.value : value;
    if (val instanceof Array) {
        val = val.reduce((ret, { value }) => {
            ret.push(value);
            return ret;
        }, []);
    }

    onChange(property)(val);
};
