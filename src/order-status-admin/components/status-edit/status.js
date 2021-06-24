/**
 * Order Status default property values.
 */

import { WPPDEV_WO_TXT_DM, TYPE_CORE, TYPE_CUSTOM } from "../../constants";
const DefaultStatus = {
    name: '',
    slug: '',
    type: TYPE_CUSTOM,
    description: '',
    days_estimation: 0,
    color: '#000000',
    background: '#eeeeee',
    icon: 'FaWrench',
    enabled_in_bulk_actions: true,
    enabled_in_reports: true,
    next_statuses:[],
    email_settings: {
        enabled: false,
        recipients: '',
        subject: '',
        message: '',
        include_order: false,
        attachments: {},
        conditions: conditions,
    },
    trigger_settings: [{
        trigger_type: '',
	    to_status: '',
        to_emails: '',
	    include_order: false,
        conditions: conditions,
    }],
};

const conditions = {
    enabled: false,
    if_overdue: false,
    from_statuses: [],
    desc: '',
};

export default DefaultStatus;