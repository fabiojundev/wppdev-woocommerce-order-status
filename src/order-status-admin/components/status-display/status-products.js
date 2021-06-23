/**
 * Show order status products.
 */

/**
 * External dependencies.
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { useSelect, useDispatch } from "@wordpress/data";
import { useState } from "@wordpress/element";
import {
    Modal,
} from '@wordpress/components';
import { WO_TEXT_DOMAIN } from "../../constants";
import { STATUS_STORE_KEY } from "../../data";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faEye, faSpinner } from '@fortawesome/free-solid-svg-icons';

/**
 * Internal dependencies.
 */

import ButtonLoader from '../button-loader';

const header = {
    name: __("Product", WO_TEXT_DOMAIN),
    quantity: __("Quantity", WO_TEXT_DOMAIN),
    in_stock: __("In Stock", WO_TEXT_DOMAIN),
    orders: __("Orders", WO_TEXT_DOMAIN),
};

const ProductRow = ({ product }) => {
    const {
        product_id,
        product_name,
        product_edit_link,
        quantity,
        in_stock,
        order_ids
    } = product;

    const [rowClassName, setRowClassName] = useState('');
    const expandRow = () => {
        const expanded = 'is-expanded';
        if (expanded == rowClassName) {
            setRowClassName('');
        }
        else {
            setRowClassName(expanded);
        }
    };

    return (
        <tr key={product_id} className={rowClassName}>
            <td className="title column-title has-row-actions column-primary page-title" data-colname={header.name}>
                <a
                    href={product_edit_link}
                    className={"wo-status-name-edit"}
                >
                    {product_name}
                </a>
                <button
                    type="button"
                    className="toggle-row"
                    onClick={expandRow}
                >
                    <span className="screen-reader-text">
                        {__('Show more details')}
                    </span>
                </button>
            </td>
            <td data-colname={header.quantity}>
                {quantity}
            </td>
            <td data-colname={header.in_stock}>
                {in_stock}
            </td>
            <td data-colname={header.orders}>
                {order_ids && order_ids.map((order, i) => {
                    const { order_id, order_edit_link } = order;
                    return (
                        <span key={order_id}>
                            {i ? ', ' : ''}
                            <a
                                href={order_edit_link}
                                className={"wo-order-link"}
                            >
                                {order_id}
                            </a>
                        </span>
                    );
                }
                )}
            </td>
        </tr>
    );
};

const StatusProductsTable = ({
    products,
}) => {

    return (products &&
        <table className={"wp-list-table widefat fixed striped table-view-list wo-order-status-products-table"}>
            <thead>
                <tr>
                    <th className="manage-column column-primary ">
                        {header.name}
                    </th>
                    <th>{header.quantity}</th>
                    <th>{header.in_stock}</th>
                    <th>{header.orders}</th>
                </tr>
            </thead>
            <tbody>
                {products.map(product =>
                    <ProductRow
                        key={product.product_id}
                        product={product}
                    />
                )}
            </tbody>
        </table>
    );
};

const StatusProducts = props => {
    const { status } = props;
    const { name, products } = status;
    const [isOpen, setOpen] = useState(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    const reqName = 'load-products-status-' + status.id;
    const inProgress = useSelect(select =>
        select(STATUS_STORE_KEY).namedRequestsInProgress(reqName),
        [reqName]
    );
    const requestError = useSelect(
        select => select(STATUS_STORE_KEY).namedRequestError(reqName)?.message,
        [reqName]
    );

    const { clearErrorMsgs, loadProducts } = useDispatch(
        STATUS_STORE_KEY
    );

    const onViewProducts = () => {
        loadProducts(status);
        openModal();
    };

    const modalProps = {
        focusOnMount: true,
        isDismissible: true,
        shouldCloseOnEsc: true,
        shouldCloseOnClickOutside: true,
        title: __('View Order Status Products - ', WO_TEXT_DOMAIN) + name,
    };

    //console.log("products", products);
    return (
        <React.Fragment>
            <ButtonLoader
                isLink
                onClick={onViewProducts}
                className="wo-table-action-ico"
                loading={inProgress}
                message={requestError}
                clear={() => clearErrorMsgs(reqName)}
                label={<FontAwesomeIcon
                    title={__('View Products', WO_TEXT_DOMAIN)}
                    icon={faEye}
                />}
            />
            { isOpen &&
                <Modal {...modalProps} onRequestClose={closeModal} className={"wo-modal"}>
                    {inProgress &&
                        <div className={"wo-spinner-large"}>
                            <FontAwesomeIcon
                                icon={faSpinner}
                                spin={true}
                                style={{ margin: "5px" }}
                            />
                        </div>
                    }
                    {products &&
                        <StatusProductsTable products={products} />
                    }
                </Modal>
            }
        </React.Fragment>
    );
};


export default StatusProducts;