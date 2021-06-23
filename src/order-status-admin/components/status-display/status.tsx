import React from "react";

interface IStatus {
    id: number,
    name: string,
    slug: string,
    tst: number,
}

const Status = (props: IStatus) => {
    // let s : IStatus= { id: 1 };
    const { id, name, slug} = props;
    return(
        <React.Fragment>
            <div>{id}</div>
            <div>{name}</div>
            <div>{slug}</div>
        </React.Fragment>
    );
};

export default Status;