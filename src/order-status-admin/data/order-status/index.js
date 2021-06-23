/**
 * Order Status redux like data store.
 * Using WP data standards.
 */

import * as selectors from "./selectors";
import * as actions from "./actions";
import reducer from "./reducer";
import * as resolvers from "./resolvers";
import { controls as wpControls } from "@wordpress/data-controls";
import localControls from "../controls";

export { default as STORE_KEY } from "./constants.js";
export const STORE_CONFIG = {
  selectors,
  actions,
  reducer,
  resolvers,
  controls: { ...wpControls, ...localControls }
};
