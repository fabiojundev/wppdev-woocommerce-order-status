/**
 * Wrapper for color picker component.
 * Open a modal to pickup a color from a palette.
 */

import React from "react";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import {
  Modal,
  ColorPicker,
  ColorIndicator,
} from '@wordpress/components';

export const distance = (v1, v2) => {
  let i, d = 0;

  for (i = 0; i < v1.length; i++) {
    d += (v1[i] - v2[i]) * (v1[i] - v2[i]);
  }
  return Math.sqrt(d);
};

export const hexToRgb = (hex) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? [
    parseInt(result[1], 16),
    parseInt(result[2], 16),
    parseInt(result[3], 16)

  ] : null;
}

export const componentToHex = (c) => {
  var hex = c.toString(16);
  return hex.length == 1 ? "0" + hex : hex;
};

export const rgbToHex = (rgb) => {
  const [r, g, b] = rgb;
  return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
};

export const chooseFontColor = (background) => {
  const back = hexToRgb(background);
  const dark = [255, 255, 255];
  const light = [0, 0, 0];
  let color = dark;
  if (distance(dark, back) < distance(light, back)) {
    color = light;
  }
  color = rgbToHex(color);

  return color;
};

export const ColorIndicatorPicker = props => {
  const {
    label,
    colorProp,
    backgroundProp,
    color,
    handleColorChange,
    className,
    disabled
  } = props;

  const [isOpen, setOpen] = useState(false);
  const openModal = () => setOpen(true);
  const closeModal = () => setOpen(false);

  const modalProps = {
    focusOnMount: true,
    isDismissible: true,
    shouldCloseOnEsc: true,
    shouldCloseOnClickOutside: true,
    title: __('Click to pick color', 'wppdev-woocommerce-order-status'),
  };

  const onClick = () => {
    if( ! disabled ) {
      openModal();
    }
  };

  return (
    <div className={className + " wo-color-picker-wrap"} >
      <label>{label}</label>
      <div>
        <ColorIndicator
          colorValue={color}
          onClick={onClick}
          disabled={disabled}
        />
      </div>
      { isOpen &&
        <Modal {...modalProps} onRequestClose={closeModal} className={"wo-color-picker-modal"}>
          <ColorPicker
            disableAlpha
            className="wo-color-picker"
            color={color}
            disabled={disabled}
            onChangeComplete={handleColorChange(colorProp, backgroundProp)}
          />
        </Modal>
      }
    </div>
  );
};

