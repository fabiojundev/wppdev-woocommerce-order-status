/**
 * Data Selectors for data store.
 */

/**
  * Get order statuses sorted.
  * 
  * @param {*} state The current state. 
  * @returns The order status object list.
*/
export const getStatuses = state => {
  return state?.statuses?.sort(statusSort) || [];
};

/**
 * Sort order status helper function.
 * 
 * @param {*} a The first order status to compare
 * @param {*} b The second order status to compare
 * @returns 
 *  -1 if a is smaller than b
 *  0 if a equal b
 *  1 if a is greater than b
 */
const statusSort = (a, b) => {
  let cmp = 0;
  if (a.id < b.id) {
    cmp = -1;
  }
  if (a.id > b.id) {
    cmp = 1;
  }
  return cmp;
}

/**
 * Get order status by ID.
 * 
 * @param {*} state The current state.
 * @param {*} id The order status ID.
 * @returns The order status object.
 */
export const getStatus = (state, id) => {
  return getStatuses(state).find(status => status.id === id);
};

export const getStatusesArray = (state, exclude_id) => {
  const statuses = state.statuses || [];
  let orderStatuses = statuses.map(status => {
    return {
      value: status.id,
      label: status.name,
    }
  });
  if (exclude_id) {
    orderStatuses = orderStatuses.filter(s => {
      return s.value != exclude_id;
    });
  }
  return orderStatuses;
};


/**
 * Requests Manager.
 */

export const requestsInProgress = (state) => {
  const requests = state.requests || [];
  return requests.filter(request => request.inProgress)
    .length > 0;
}

/**
 * Get requests in progress by single requestName.
 * 
 * @param {*} state The current state.
 * @param {*} requestName The request name to get request info.
 * @returns The request info.
 */
export const namedRequestsInProgress = (
  state,
  requestName
) => {
  const requests = state.requests || [];

  const singleNamedRequestInProgress = (singleRequestName) => {
    return requests.find(request =>
      request.name === singleRequestName && request.inProgress
    ) !== undefined;
  }

  if (Array.isArray(requestName)) {
    return requestName.some(singleNamedRequestInProgress)
  }

  return singleNamedRequestInProgress(requestName);
};

/**
 * Get error info from request.
 * 
 * @param {*} state The current state.
 * @param {*} requestName The request name to get request info.
 * @returns The request error info.
 */
export const namedRequestError = (state, requestName) => {
  const requests = state.requests || [];
  return requests.find(
    request =>
      request.name === requestName && request.error !== null
  )?.error;
};

/**
 * Get request info.
 * 
 * @param {*} state The current state.
 * @param {*} requestName The request name to get request info.
 * @returns The request info.
 */
export const getRequest = (state, requestName) => {
  const requests = state.requests || [];
  return requests.find(
    request =>
      request.name === requestName
  );
};