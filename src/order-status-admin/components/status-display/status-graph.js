/**
 * Show order statuses in digraph view.
 */

import React from 'react';
import { useState } from "@wordpress/element";
import { Graphviz } from 'graphviz-react';
import { __ } from '@wordpress/i18n';
import { WO_TEXT_DOMAIN } from '../../constants/index.js';

import {
    Modal,
    Button,
} from '@wordpress/components';

export default function StatusGraph({ statuses }) {

    const [isOpen, setOpen] = useState(false);
    const openModal = () => setOpen(true);
    const closeModal = () => setOpen(false);

    const modalProps = {
        focusOnMount: true,
        isDismissible: true,
        shouldCloseOnEsc: true,
        shouldCloseOnClickOutside: true,
        title: __('Order Statuses Overview', WO_TEXT_DOMAIN),
    };

    const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0)
    const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)

    const options = {
        width: Math.min(vw - 20, 500),
        height: Math.min(vh, 600),
    };

    let digraph = statuses.map(status => {
        const { slug, name, next_statuses, color, background } = status;
        const from = name;
        let ret = ` "${from}" [fillcolor="${background}" fontcolor="${color}" ]; \n`;

        if (next_statuses?.length) {
            ret += next_statuses.map(next => {
                const next_status = statuses.find(s => s.id == next);
                const to = next_status?.name ? next_status.name : '';
                return ` "${from}" -> "${to}"; `;
            }).join('\n');
        }

        return ret;
    }).filter(line => line != '').join('\n');

    const orientation = vw > vh ? 'rankdir=LR;' : '';

    digraph = `digraph {
        node [margin=0 style=filled fontname=arial labelfontcolor=white fontcolor=white color=white]
        ${digraph}
    }`;

    // console.log(digraph);
    return (
        <React.Fragment>
            <Button
                isSecondary
                onClick={() => openModal()}
            >
                {__('Overview', WO_TEXT_DOMAIN)}
            </Button>
            { isOpen &&
                <Modal 
                    {...modalProps} 
                    onRequestClose={closeModal} 
                    className={"wo-graph-modal wo-modal"}
                >
                    <div className="wo-order-status-graph">
                        <Graphviz dot={digraph} options={options} />
                    </div>
                </Modal>
            }
        </React.Fragment>
    );
};
