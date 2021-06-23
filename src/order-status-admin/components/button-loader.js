/**
 * Button with loading feedback component.
 * Show feedback message using snackbar WP component.
 */

import React from "react";
import {
  Button,
  Snackbar,
} from '@wordpress/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSpinner } from '@fortawesome/free-solid-svg-icons';

export default function ButtonLoader(props) {

  const {
    loading,
    label,
    message,
    clear,
  } = props;
  const btnProps = {
    ...props,
    disabled: loading,
    loading: undefined,
    clear: undefined,
    message: undefined
  };

  const Feedback = () => {
    let ret = null;
    if (message) {
      setTimeout(() => clear(), 20000);

      ret =
        <span onClick={clear} className={"wo-feedback-msg"}>
          <Snackbar explicitDismiss={true}>{message}</Snackbar>
        </span>
        ;
    }
    return ret;
  };
  return (
    <React.Fragment>
      <Button
        {...btnProps}
      >
        {label}
        {loading &&
          <FontAwesomeIcon
            icon={faSpinner}
            spin={true}
            style={{ margin: "0 5px" }}
          />
        }
      </Button>
      <Feedback />
    </React.Fragment>
  );
}
