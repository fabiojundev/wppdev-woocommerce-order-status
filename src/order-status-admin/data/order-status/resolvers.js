/**
 * Resolvers for data store.
 */

import { fetch } from "../controls";
import { loadStatuses } from "./actions";
import { getResourcePath } from "./utils";

/**
 * Get all order statuses from Rest API.
 * Load the results in data store using action.
 * 
 * @returns The order statuses.
 */
export function* getStatuses() {
  const statuses = yield fetch(getResourcePath());
  if (statuses) {
    return loadStatuses(statuses);
  }
  return;
}

/**
 * Get corresponding selector functions.
 * 
 * @returns 
 */
export function requestsInProgress() {
    return;
}

export function namedRequestsInProgress(name) {
    return;
}

export function namedRequestError(name) {
    return;
}

export function getRequest(name) {
    return;
}