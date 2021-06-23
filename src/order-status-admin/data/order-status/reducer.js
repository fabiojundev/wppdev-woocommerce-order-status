/**
 * Data store reducer.
 */

import TYPES from "./action-types";

const {
  CREATE,
  UPDATE,
  DELETE,
  LOAD,
  IMPORT,
  REQUEST_STARTED,
  REQUEST_FINISHED,
  REQUEST_FAILED,
  CLEAR_ERROR_MSGS,
} = TYPES;

/**
 * Initial data store state.
 */
const initialState = {
  statuses: [],
  requests: [],
};

/**
 * The order statuses state reducer.
 * Handle order status and requests to REST API.
 * 
 * @param {*} state The current state.
 * @param {*} payload The action payload.
 * @returns The new state.
 */
const reducer = (
  state = initialState,
  payload
) => {
  const { type, statuses: incomingStatuses, status, statusId, request } = payload;

  switch (type) {
    case CREATE:
      return {
        ...state,
        statuses: [...state.statuses, status]
      };

    case UPDATE:
      return {
        ...state,
        statuses: state.statuses
          .filter(existing => existing.id !== status.id)
          .concat([status])
      };

    case DELETE:
      return {
        ...state,
        statuses: state.statuses.filter(existing => existing.id !== statusId)
      };

    case LOAD:
      return {
        ...state,
        statuses: incomingStatuses
      };

    case IMPORT:
      return {
        ...state,
        statuses: incomingStatuses
      };

    case REQUEST_STARTED:
      const existingCall = state.requests.find(req => req.name === request.name)
      if (existingCall) {
        return {
          ...state,
          requests: [
            ...state.requests.filter(req => req.name !== request.name),
            request
          ],
        }
      }
      return {
        ...state,
        requests: [...state.requests, request],
      };

    case REQUEST_FINISHED:
      const newState = {
        ...state,
        requests: [
          ...state.requests.filter(req => req.name !== request.name),
          request
        ],
      };
      return newState;

    case REQUEST_FAILED:
      return {
        ...state,
        requests: state.requests.map(req =>
          req.name === request.name
            ? {
              ...req,
              error: request.error,
              message: request.error ? request.error.message : '',
              inProgress: false,
            }
            : req
        ),
      };

    case CLEAR_ERROR_MSGS:
      return {
        ...state,
        requests: state.requests.map(req =>
          req.name === request.name
            ? {
              ...req,
              error: null,
              message: null,
              inProgress: false,
            }
            : req
        ),
      };

    default:
      return state;
  }
};

export default reducer;
