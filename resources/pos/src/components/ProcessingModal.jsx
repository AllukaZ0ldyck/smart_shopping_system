// resources/pos/src/components/ProcessingModal.jsx
// import React, { useEffect, useState, useRef } from "react";
import { Modal, Spinner } from "react-bootstrap-v5";

const ProcessingModal = ({ show, onCancel, message }) => {
    return (
        <Modal show={show} centered backdrop="static">
            <Modal.Header>
                <Modal.Title>Processing Payment</Modal.Title>
            </Modal.Header>
            <Modal.Body className="text-center">
                <Spinner animation="border" role="status" />
                <div className="mt-3">{message || 'Please complete payment in the opened window. Waiting for confirmation...'}</div>
                <div className="small text-muted mt-2">You can close this window when payment completes.</div>
            </Modal.Body>
            <Modal.Footer>
                <button className="btn btn-secondary" onClick={onCancel}>Cancel</button>
            </Modal.Footer>
        </Modal>
    );
};

export default ProcessingModal;
