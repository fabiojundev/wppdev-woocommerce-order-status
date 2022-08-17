/**
 * Order statuses admin list table view.
 */

/**
 * External dependencies.
 */
import React from "react";
import { __ } from "@wordpress/i18n";
import { useSelect, useDispatch } from "@wordpress/data";
import { useState } from "@wordpress/element";
import {
  Button,
  SelectControl,
  Tooltip,
  Card,
} from '@wordpress/components';
import ButtonLoader from '../button-loader';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faLevelUpAlt, faEnvelope, faPlusCircle } from '@fortawesome/free-solid-svg-icons';

/**
 * Internal dependencies.
 */
import OrderStatusDelete from '../status-edit/status-delete';
import { STATUS_STORE_KEY } from "../../data";
import { TYPE_CORE, TYPE_CUSTOM, TAB_SETTINGS, TAB_MAIL, TAB_TRIGGERS } from "../../constants";
import { sformat } from "../../utils";
import StatusGraph from './status-graph';
import { IconPickerItem } from 'react-fa-icon-picker';
import StatusProducts from './status-products';
import {
  StatusSettings,
  EmailSettings,
  StatusTriggers
} from '../status-edit';

const header = {
  icon: __("Icon | Type", 'wppdev-woocommerce-order-status'),
  name: __("Status Name", 'wppdev-woocommerce-order-status'),
  slug: __("Slug", 'wppdev-woocommerce-order-status'),
  description: __("Description", 'wppdev-woocommerce-order-status'),
  next_statuses: __("Next", 'wppdev-woocommerce-order-status'),
  type: __("Type", 'wppdev-woocommerce-order-status'),
  trigger: __("Trigger", 'wppdev-woocommerce-order-status'),
  orders_count: __("Orders | Products", 'wppdev-woocommerce-order-status'),
  products: __("Products", 'wppdev-woocommerce-order-status'),
};


const StatusList = (props) => {

  const { statuses } = useSelect(select => {
    return {
      statuses: select(STATUS_STORE_KEY).getStatuses(),
    };
  }, []);

  const orderStatuses = useSelect(
    select => select(STATUS_STORE_KEY).getStatusesArray(),
    []
  );

  const {
    clearErrorMsgs,
    importStatuses,
    loadProducts
  } = useDispatch(STATUS_STORE_KEY);

  const { edit } = props;

  const [isEditing, setIsEditing] = useState(edit?.id ? TAB_SETTINGS : false);

  const [editedStatus, setEditedStatus] = useState(edit);

  const [importId, setImportId] = useState('');


  const reqName = 'import-statuses';
  const request = useSelect(
    select => select(STATUS_STORE_KEY).getRequest(reqName),
    [reqName]
  );
  const { inProgress, message } = request || { inProgress: false, message: '' };

  const onCreate = () => {
    setEditedStatus({});
    setIsEditing(TAB_SETTINGS);
  };

  const onEdit = (status, tabname) => {
    setEditedStatus(status);
    setIsEditing(tabname);
  };

  const onCancel = () => {
    setIsEditing(false);
  };

  const onImport = () => {
    if (importId) {
      importStatuses(importId);
    }
  };

  const onLoadProducts = status => {
    setIsEditing(false);
    loadProducts(status);

  };

  const title = sformat('{0}{1} ',
    __('Edit Order Status', 'wppdev-woocommerce-order-status'),
    editedStatus?.name ? ': ' + editedStatus.name : ''
  );

  const getEditTab = tabname => {
    switch (tabname) {
      case TAB_SETTINGS:
        return (
          <StatusSettings
            status={editedStatus}
            orderStatuses={orderStatuses}
            onCancel={onCancel}
          />
        );
      case TAB_MAIL:
        return (
          <EmailSettings
            status={editedStatus}
            orderStatuses={orderStatuses}
            onCancel={onCancel}
          />
        );
      case TAB_TRIGGERS:
        return (
          <StatusTriggers
            status={editedStatus}
            orderStatuses={orderStatuses}
            onCancel={onCancel}
          />
        );
    }
  };

  return isEditing ? (
    <React.Fragment>
      <h1>
        {title}
        <Button
          isLink
          onClick={onCancel}
          disabled={inProgress}
        >
          <FontAwesomeIcon
            title={__('Back', 'wppdev-woocommerce-order-status')}
            icon={faLevelUpAlt}
            className={"wo-back"}
          />
        </Button>
      </h1>
      <Card>
        {getEditTab(isEditing)}
      </Card>
    </React.Fragment>
  ) : (
    <React.Fragment>
      <h1 className={"wp-heading-inline"}>{__('Order Status Workflow', 'wppdev-woocommerce-order-status')}</h1>
      <div className="wo-flex">
        <Button
          isSecondary
          onClick={onCreate}
        >
          {__('Add New', 'wppdev-woocommerce-order-status')}
        </Button>
        <StatusGraph
          statuses={statuses}
        />
        <SelectControl
          id="importId"
          name="importId"
          value={importId}
          options={[
            { label: __('Select', 'wppdev-woocommerce-order-status'), value: '' },
            { label: __('Reset Core Statuses', 'wppdev-woocommerce-order-status'), value: 'core' },
            { label: __('Import Manufactory Preset', 'wppdev-woocommerce-order-status'), value: 'manufactory' },
            { label: __('Import Food Delivery Preset', 'wppdev-woocommerce-order-status'), value: 'food_delivery' },
          ]}
          onChange={value => setImportId(value)}
        />
        <ButtonLoader
          isSecondary
          disabled={importId ? true : false}
          onClick={onImport}
          label={__('Apply', 'wppdev-woocommerce-order-status')}
          loading={inProgress}
          message={message}
          clear={() => clearErrorMsgs(reqName)}
        />
      </div>
      <table className={"wp-list-table widefat fixed striped table-view-list wo-order-status-table"}>
        <thead>
          <tr>
            <th className="manage-column column-primary ">
              {header.name}
            </th>
            <th>{header.next_statuses}</th>
            <th>{header.icon}</th>
            <th>{header.trigger}</th>
            <th>{header.orders_count}</th>
          </tr>
        </thead>
        <tbody>{statuses.map((status, index) => {
          return (
            <StatusRow
              key={status.id}
              status={status}
              statuses={statuses}
              onEdit={onEdit}
              onDelete={() => onDelete(status.id)}
              onLoadProducts={() => onLoadProducts(status)}
              className={index % 2 ? "wo-alternate" : ''}
            />
          )
        })}
        </tbody>
      </table>
    </React.Fragment>
  );
};

const StatusRow = (props) => {
  const {
    onEdit,
    status,
    statuses,
  } = props;

  const {
    id = 0,
    name = "",
    slug = "",
    description = "",
    next_statuses = [],
    icon = 'FaAdobe',
    color = '#777',
    background = '#e5e5e5',
    type = TYPE_CUSTOM,
    orders_count = 0,
    orders_link = '#',
    products = [],
  } = status;

  const position = 'top center';

  return (
    <tr>
      <td className="title column-title has-row-actions column-primary page-title" data-colname={header.name}>
        <a
          href="#"
          onClick={() => onEdit(status, TAB_SETTINGS)}
          className={"wo-status-name-edit"}
        >
          <Tooltip text={description} position={position}>
            <span
              className={'wo-slug'}
              style={{ background: background, color: color }}
            >
              {name}
            </span>
          </Tooltip>
        </a>
        <div className="row-actions">
          <span className="edit">
            <a
              onClick={() => onEdit(status, TAB_SETTINGS)}
              data-id={id}
            >
              {__('Edit', 'wppdev-woocommerce-order-status')}
            </a>
          </span>
          {TYPE_CORE != type &&
            <span className="delete">
              {' | '}
              <OrderStatusDelete
                status={status}
              />
            </span>
          }
        </div>
        <button type="button" className="toggle-row">
          <span className="screen-reader-text">
            {__('Show more details')}
          </span>
        </button>
      </td>
      <td data-colname={header.next_statuses}>
        {next_statuses && next_statuses.map(next => {
          const found = statuses.find(st => st.id === next);
          if (!found) {
            return '';
          }
          const { name, background, color, description } = found;
          return (
            <Tooltip text={description} position={position} key={next}>
              <span
                className={'wo-slug'}
                style={{ background: background, color: color }}
              >
                {`${name} `}
              </span>
            </Tooltip>
          );
        }
        )}
      </td>
      <td data-colname={header.icon}>
        {icon &&
          <Tooltip text={description} position={position}>
            <span
              className={'wo-slug'}
              style={{ background: background, color: color }}
            >
              <IconPickerItem
                icon={icon}
                size={12}
                color={color}
              />
            </span>
          </Tooltip>
        }
        <span className={"wo-type wo-" + type}>{type}</span>
      </td>
      <td data-colname={header.trigger}>
        <div className={"wo-double-col"}>
          <Button
            isLink
            title={__('Trigger Email', 'wppdev-woocommerce-order-status')}
            onClick={() => onEdit(status, TAB_MAIL)}
            className={"wo-table-action-ico"}
          >
            <FontAwesomeIcon
              icon={faEnvelope}
            />
          </Button>
          {/* <Button
            isLink
            title={__('Other Triggers', 'wppdev-woocommerce-order-status')}
            onClick={() => onEdit(status, TAB_TRIGGERS)}
            className={"wo-table-action-ico"}
          >
            <FontAwesomeIcon
              icon={faPlusCircle}
            />
          </Button> */}
        </div>
      </td>
      <td data-colname={header.orders_count}>
        <div className={"wo-double-col"}>
          <a
            href={orders_link}
            target="_blank"
          >
            {orders_count}
          </a>
          <StatusProducts status={status} />

        </div>
      </td>
    </tr >
  );
};

export default StatusList;
