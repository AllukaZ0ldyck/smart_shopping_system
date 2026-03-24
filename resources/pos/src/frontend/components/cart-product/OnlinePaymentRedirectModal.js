import React, { useCallback, useEffect } from "react";
import SweetAlert from "react-bootstrap-sweetalert";

const OnlinePaymentRedirectModal = ({ onCancel, onConfirm }) => {
    const escFunction = useCallback(
        (event) => {
            if (event.keyCode === 27) {
                onCancel(false);
            }
        },
        [onCancel]
    );

    useEffect(() => {
        document.addEventListener("keydown", escFunction, false);
        return () => {
            document.removeEventListener("keydown", escFunction, false);
        };
    }, [escFunction]);

    return (
        <SweetAlert
            custom
            warning
            showCancel
            focusCancelBtn
            title="Online payment"
            confirmBtnText="Continue"
            cancelBtnText="Cancel"
            confirmBtnBsStyle="success mb-3 fs-5 rounded"
            cancelBtnBsStyle="secondary mb-3 fs-5 rounded text-white"
            onConfirm={onConfirm}
            onCancel={onCancel}
        >
            <span className="sweet-text">
                You&apos;ll be redirected to online payment partner.
            </span>
        </SweetAlert>
    );
};

export default OnlinePaymentRedirectModal;
