/**
 * Register order status store.
 */

import {
  STORE_KEY as STATUS_STORE_KEY,
  STORE_CONFIG as statusConfig
} from "./order-status";
import { registerStore } from "@wordpress/data";

registerStore(STATUS_STORE_KEY, statusConfig);

export { STATUS_STORE_KEY };
