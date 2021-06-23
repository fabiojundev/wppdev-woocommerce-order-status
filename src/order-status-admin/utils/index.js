// Util functions

/**
 * Format string with parameters
 * 
 * @param string str2Format The string with {0}... {n} placeholders
 * @param  {...any} args The corresponding values to the placeholders
 * @returns 
 */
export const sformat = (str2Format, ...args) => 
  str2Format.replace(/(\{\d+\})/g, a => args[+(a.substr(1, a.length - 2)) || 0] );