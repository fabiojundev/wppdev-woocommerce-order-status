/**
 * Wrapper for react icon picker component.
 * 
 */
import React from "react";
import {
  Button,
  TextControl,
  Dropdown,
} from '@wordpress/components';
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { fas } from '@fortawesome/free-solid-svg-icons';

library.add(fas);
class ReactIconPicker extends React.PureComponent {
  constructor(props) {
    super(props);

    this.toggle = this.toggle.bind(this);
    this.searchIconHandler = this.searchIconHandler.bind(this);
    this.pickIconHandler = this.pickIconHandler.bind(this);

    this.state = {
      dropdownOpen: false,
      icons: { ...fas },
      pickedIcon: {
        prefix: 'fas',
        iconName: 'coffee'
      },
      search: ''
    };
  }

  toggle() {
    this.setState(prevState => ({
      dropdownOpen: !prevState.dropdownOpen
    }));
  }

  searchIconHandler(event) {
    this.setState({
      search: event.target.value
    })
  }

  pickIconHandler(icon) {
    this.toggle()
    this.setState({
      pickedIcon: icon
    })
    this.props.pickIcon(icon)
  }

  render() {
    const { width, height, color, text } = this.props;
    const styles = {
      iconContainer: {
        width: '200px',
        height: '250px',
        overflow: 'scroll',
        padding: '8px'
      },
      iconSearchInput: {
        margin: '0 0 5px 0'
      },
      iconItem: {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        margin: '6px',
        width: '32px',
        height: '32px',
        borderRadius: '50px',
        fontSize: '18px'
      }
    }

    const iconLists = Object.keys(this.state.icons)
      .filter(
        key => key !== "faFontAwesomeLogoFull" && this.state.icons[key].iconName.includes(this.state.search)
      )
      .map(key => (
        <Button key={key} isSecondary style={styles.iconItem} onClick={() => this.pickIconHandler(this.state.icons[key])} >
          <FontAwesomeIcon icon={[this.state.icons[key].prefix, this.state.icons[key].iconName]} />
        </Button>
      ));

    return (
      <Dropdown isOpen={this.state.dropdownOpen} onClick={this.toggle}>
        <FontAwesomeIcon icon={[this.state.pickedIcon.prefix, this.state.pickedIcon.iconName]} />
        <select style={styles.iconContainer}>
          <TextControl name="icon" placeholder="Search Icon" value={this.state.search}
            onChange={this.searchIconHandler} style={styles.iconSearchInput} />
          {iconLists}
        </select>
      </Dropdown>
    );
  }
}

export default ReactIconPicker;