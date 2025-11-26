import React, { useState, useEffect, useRef } from "react";
import { Modal } from "react-bootstrap-v5";

const HitPayModal = ({ sale, show, onClose }) => {
    const [loading, setLoading] = useState(false);
    const pollingRef = useRef(null);

    const startPolling = (reference) => {
        pollingRef.current = setInterval(async () => {
            const res = await fetch(`/api/sale/status/${reference}`);
            const data = await res.json();

            if (data.payment_status === 1) {
                clearInterval(pollingRef.current);

                printReceipt(data.sale);
                onClose();

                window.location.reload(); // reset POS
            }
        }, 3000);
    };

    const payWithHitPay = async () => {
        setLoading(true);

        // Create popup instantly
        const popup = window.open("", "_blank");

        const response = await fetch("/api/hitpay/create", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                sale_id: sale.id,
                amount: sale.grand_total
            }),
        });

        const data = await response.json();

        if (data.success) {
            popup.location.href = data.redirect_url;
            startPolling(data.reference);
        } else {
            popup.close();
            alert("Failed to start HitPay payment.");
        }
    };

    return (
        <Modal show={show} centered>
            <Modal.Header>
                <h4>HitPay Payment</h4>
            </Modal.Header>

            <Modal.Body>
                <p>Total: {sale?.grand_total}</p>

                <button
                    className="btn btn-primary w-100"
                    onClick={payWithHitPay}
                    disabled={loading}
                >
                    {loading ? "Processing..." : "Pay with HitPay"}
                </button>
            </Modal.Body>

            <Modal.Footer>
                <button className="btn btn-secondary" onClick={onClose}>
                    Close
                </button>
            </Modal.Footer>
        </Modal>
    );
};

export default HitPayModal;
