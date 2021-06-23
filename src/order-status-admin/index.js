/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { useDispatch } from "@wordpress/data";

/**
 * Internal dependencies
 */
import './index.scss';
import { StatusList } from './components/status-display';
import { STATUS_STORE_KEY } from "./data";

/**
 * App to manage WC order statuses.
 * 
 * @param { 
 *  order_statuses: WC order statuses list
 *  edit: order status ID to edit 
 * } props  
 * @returns The App element
 */
const App = (props) => {
  const { order_statuses, edit } = props.data_wo;
  const { loadStatuses } = useDispatch(STATUS_STORE_KEY);
  loadStatuses(order_statuses);

  return (
    <div className="App">
      <StatusList edit={edit} />
    </div>
  );
};

document.addEventListener('DOMContentLoaded', function () {
  const target = document.getElementById('wo-react-app');
  const data = JSON.parse(target.getAttribute('data-wo'));

  if (target) {
    ReactDOM.render(<App data_wo={data} />, target);
  }
});
