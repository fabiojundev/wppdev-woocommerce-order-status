/**
 * Register order status store.
 */

import {
  STORE_KEY as STATUS_STORE_KEY,
  STORE_CONFIG as statusConfig
} from "./order-status";
import { register, createReduxStore } from "@wordpress/data";

const store = createReduxStore( STATUS_STORE_KEY, statusConfig );
register( store );

export { STATUS_STORE_KEY };
