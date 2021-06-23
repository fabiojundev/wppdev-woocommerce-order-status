/**
 * Server REST API path functions.
 */

/**
 * Get order status rest api url.
 * 
 * @param int|undefined id The order status id to get url.
 * @returns The resource url.
 */
export const getResourcePath = id => {
  const root = `${window.location.origin}/wp-json/wo/v1/order-status`;
  let url = id ? `${root}/${id}` : root;
  return url;
};

/**
 * Get import order statuses url.
 * 
 * @returns 
 */
export const getImportPath = () => {
  return getResourcePath() + '/import';
};

/**
 * Get order status products url.
 * 
 * @param int|undefined id The order status ID to get products.
 * @returns 
 */
export const getProductsPath = (id) => {
  return getResourcePath() + '-products/' + id;
};
