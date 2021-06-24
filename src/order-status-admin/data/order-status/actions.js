/**
 * Order status data store actions.
 */

import { getResourcePath, getImportPath, getProductsPath } from "./utils";
import { fetch } from "../controls";
import { select } from "@wordpress/data-controls";
import TYPES from "./action-types";
import STORE_KEY from "./constants";

import { __ } from '@wordpress/i18n';
import { WPPDEV_WO_TXT_DM } from '../../constants/index.js';

const {
  UPDATE,
  CREATE,
  DELETE,
  LOAD,
  LOAD_PRODUCTS,
  IMPORT,
  REQUEST_STARTED,
  REQUEST_FINISHED,
  REQUEST_FAILED,
  CLEAR_ERROR_MSGS,
} = TYPES;

/**
 * Create a new order status action creator.
 * Async call to rest url, generating start and finish actions.
 * 
 * @param {*} status The status object.
 * @returns Action payload, or error message.
 */
export function* createStatus(status) {
  // console.log("Create Status", status, getResourcePath(status.id));
  const name = 'create-status';
  let ret = {
    message: __('Create Order Status Failed', WPPDEV_WO_TXT_DM),
  };

  try {
    yield requestStarted(name);
    const result = yield fetch(getResourcePath(), {
      method: "POST",
      body: status
    });

    if (result && !result.code) {
      const {
        status,
        message,
      } = result;
      yield requestFinished(name, message);
      return {
        type: CREATE,
        status
      };
    }
    ret = { ...ret, ...result };
  } catch (error) {
    ret = { ...ret, ...error };
  }
  return requestFailed(name, ret);
}

/**
 * Update an order status action creator.
 * Async call to rest url, generating start and finish actions.
 * 
 * @param {*} status The status object.
 * @returns Action payload, or error message.
 */
export function* updateStatus(status) {
  // console.log("update Status", status, getResourcePath(status.id));
  const name = 'update-status-' + status.id;
  let ret = {
    message: __('Update Order Status Failed', WPPDEV_WO_TXT_DM),
  };

  try {
    yield requestStarted(name);
    const result = yield fetch(getResourcePath(status.id), {
      method: "PUT",
      body: status
    });

    if (result && !result.code) {
      const {
        status,
        message,
      } = result;

      yield requestFinished(name, message);
      return {
        type: UPDATE,
        status
      };
    }
    ret = { ...ret, ...result };
  } catch (error) {
    ret = { ...ret, ...error };
  }
  return requestFailed(name, ret);
}

/**
 * Delete an order status action creator.
 * Async call to rest url, generating start and finish actions.
 * 
 * @param {*} status The status object.
 * @returns Action payload, or error message.
 */
export function* deleteStatus(statusId, reassign) {

  const status = yield select(STORE_KEY, "getStatus", statusId);
  // console.log("Delete Status", status, getResourcePath(status.id));
  const name = 'delete-status-' + status.id;
  let ret = {
    message: __('Delete Order Status Failed', WPPDEV_WO_TXT_DM),
  };

  try {
    yield requestStarted(name);
    const result = yield fetch(getResourcePath(status.id), {
      method: "DELETE",
      body: { reassign: reassign }
    });

    if (result && !result.code) {
      const { message = '' } = result;

      yield requestFinished(name, message);
      return {
        type: DELETE,
        statusId
      };
    }
    ret = { ...ret, ...result };
  } catch (error) {
    ret = { ...ret, ...error };
  }
  return requestFailed(name, ret);
}

/**
 * Load order status action.
 * 
 * @param {*} statuses The order status array to load.
 * @returns action payload.
 */
export const loadStatuses = statuses => {
  return {
    type: LOAD,
    statuses
  };
};

/**
 * Load order status produts action.
 * 
 * @param {*} status The order status to load products to.
 * @returns action payload.
 */
export function* loadProducts(status) {
  const name = 'load-products-status-' + status.id;
  let ret = {
    message: __('Load Order Status Products Failed', WPPDEV_WO_TXT_DM),
  };

  if (status.products) {
    return requestFinished(name, __('Already loaded', WPPDEV_WO_TXT_DM));
  }

  try {
    yield requestStarted(name);
    const result = yield fetch(getProductsPath(status.id), {
      method: "GET",
    });

    if (result && !result.code) {
      const {
        products,
        message,
      } = result;

      yield requestFinished(name, message);
      return {
        type: UPDATE,
        status: {
          ...status,
          products,
        },
      };
    }
    ret = { ...ret, ...result };
  } catch (error) {
    ret = { ...ret, ...error };
  }
  return requestFailed(name, ret);
}

/**
 * Import Order Statuses from template.
 * 
 * @param string importId The template import ID.
 * @returns The action payload.
 */
export function* importStatuses(importId) {
  const name = 'import-statuses';
  let ret = {
    message: __('Import Order Statuses Failed', WPPDEV_WO_TXT_DM),
  };

  try {
    yield requestStarted(name);
    const result = yield fetch(getImportPath(), {
      method: "PUT",
      body: { action: name, import_id: importId }
    });

    if (result && !result.code) {
      const {
        statuses,
        message,
      } = result;

      yield requestFinished(name, message);
      return {
        type: IMPORT,
        statuses
      };
    }
    ret = { ...ret, ...result };
  } catch (error) {
    ret = { ...ret, ...error };
  }

  return requestFailed(name, ret);
};

/**
 * The request has started action.
 * 
 * @param {*} requestName The request name ID.
 * @returns 
 */
export const requestStarted = (requestName) => {
  return {
    type: REQUEST_STARTED,
    request: {
      name: requestName,
      inProgress: true,
    },
  }
};

/**
 * The request has finished action.
 * 
 * @param {*} requestName The request name ID.
 * @returns 
 */
export const requestFinished = (requestName, message) => {
  return {
    type: REQUEST_FINISHED,
    request: {
      name: requestName,
      inProgress: false,
      message: message,
    },
  }
};

/**
 * The request has failed action.
 * 
 * @param {*} requestName The request name ID.
 * @returns 
 */
 export const requestFailed = (requestName, error) => {
  return {
    type: REQUEST_FAILED,
    request: {
      name: requestName,
      inProgress: false,
      error,
    },
  }
};

/**
 * Clear request name error messages.
 * 
 * @param {*} requestName The request name ID.
 * @returns 
 */
export const clearErrorMsgs = (requestName) => {
  return {
    type: CLEAR_ERROR_MSGS,
    request: {
      name: requestName,
    },
  }
};

