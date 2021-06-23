/**
 * Custom fetch action generator.
 * 
 * @param string path 
 * @param {*} options 
 * @returns 
 */
export const fetch = (path, options = {}) => {

  options.headers = new Headers({
    "Content-Type": "application/json",
    "X-WP-Nonce": window.wpApiSettings.nonce, //Set in Controller_Plugin enqueue scripts
  });
  if (options.body) {
    options.body = JSON.stringify(options.body);
  }
  options.credentials = "include";
  // console.log("controls fetch", path, options.headers);
  return {
    type: "FETCH",
    path,
    options
  };
};

export default {
  FETCH({ path, options }) {
    return new Promise((resolve, reject) => {
      window
        .fetch(path, options)
        .then(response => {
          return response.json();
        })
        .then(result => resolve(result))
        .catch(error => {
          return reject(error);
        });
    });
  }
};
