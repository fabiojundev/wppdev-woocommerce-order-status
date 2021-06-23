import './index.scss';
/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { IconPickerItem } from 'react-fa-icon-picker';

/**
 * Add order status action buttons in WC Orders Table.
 */
document.addEventListener('DOMContentLoaded', function () {
  const shortcode_containers = document.querySelectorAll('.wo-status-icon');

  for (let i = 0; i < shortcode_containers.length; ++i) {
    const icon_container = shortcode_containers[i];
    const data = JSON.parse(icon_container.getAttribute('data-wo'));
    const {
      icon = 'FaWrench',
      color = '#777',
      background = '#eee'
    } = data;

    ReactDOM.render(
      <IconPickerItem
        icon={icon}
        size={11}
        color={color}
        containerStyles={{
          background: background,
          borderColor: background,
        }}
      />,
      shortcode_containers[i]
    );
  }
});
