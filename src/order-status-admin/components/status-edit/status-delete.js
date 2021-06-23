
/**
 * Order status delete manager.
 * Verify is there is any order using the status, before delete.
 * Show option to reassign to another order status.
 */

import React from "react";
import { useState } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { STATUS_STORE_KEY } from "../../data";
import { __ } from "@wordpress/i18n";
import { WO_TEXT_DOMAIN } from "../../constants";
import { ReactSelect, onSelect } from '../react-select';
import ButtonLoader from '../button-loader';

import {
    Modal,
    Button,
} from '@wordpress/components';

export default function OrderStatusDelete(props) {
    const {
        status,
    } = props;
    const {
        id = 0,
        name = '',
        orders_count = 0,
    } = status;

    const [isOpen, setOpen] = useState(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    const orderStatuses = useSelect(
        select => select(STATUS_STORE_KEY).getStatusesArray(status.id),
        []
    );

    const { deleteStatus, clearErrorMsgs } = useDispatch(
        STATUS_STORE_KEY
    );

    const reqName = 'delete-status-' + id;
    const inProgress = useSelect(select =>
        select(STATUS_STORE_KEY).namedRequestsInProgress(reqName),
        [reqName]
    );
    const requestError = useSelect(
        select => select(STATUS_STORE_KEY).namedRequestError(reqName)?.message,
        [reqName]
    );

    const modalProps = {
        focusOnMount: true,
        isDismissible: true,
        shouldCloseOnEsc: true,
        shouldCloseOnClickOutside: true,
        title: __('Delete this custom order status?', WO_TEXT_DOMAIN),
    };

    const [reassign, setReassign] = useState('');
    const onChange = property => value => {
        setReassign(value);
    };

    const onDelete = id => {
        if (orders_count) {
            openModal();
        }
        else {
            deleteStatus(id);
        }
    };

    const confirmDelete = () => {
        deleteStatus(id, reassign);
    };

    return (
        <React.Fragment>
            <ButtonLoader
                isLink
                onClick={() => onDelete(id)}
                className="wo-table-action-btn wo-delete"
                loading={inProgress}
                message={requestError}
                clear={() => clearErrorMsgs(reqName)}
                label={__('Delete', WO_TEXT_DOMAIN)}
            />
            {isOpen &&
                <Modal {...modalProps} onRequestClose={closeModal} className={"wo-order-delete-modal wo-modal"}>
                    <h4>
                        {
                            __('There are ', WO_TEXT_DOMAIN) + orders_count +
                            __(' orders with status ', WO_TEXT_DOMAIN) + name
                        }
                    </h4>
                    <label>{__('Reassign to Status', WO_TEXT_DOMAIN)}</label>
                    <ReactSelect
                        id="reassign"
                        name="reassign"
                        value={reassign}
                        options={orderStatuses}
                        onChange={onSelect('reassign', onChange)}
                    />
                    <Button
                        isSecondary
                        onClick={closeModal}
                    >
                        {__('Cancel', WO_TEXT_DOMAIN)}
                    </Button>
                    <ButtonLoader
                        isDestructive
                        onClick={confirmDelete}
                        className="wo-table-action-btn wo-delete"
                        loading={inProgress}
                        message={requestError}
                        clear={() => clearErrorMsgs(reqName)}
                        label={__('Reassign and Delete', WO_TEXT_DOMAIN)}
                    />
                </Modal>
            }
        </React.Fragment>
    );
}