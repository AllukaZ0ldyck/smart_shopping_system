<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 40px; }
    </style>
</head>
<body>

@php
    $reference = request('reference');
    $status = request('status');
@endphp

<h2>
    @if($status === 'success')
        ✅ Payment Successful
    @else
        ❌ Payment Failed
    @endif
</h2>

<p>This window will close automatically...</p>

<script>
    // Prepare data to send to the parent window
    const payload = {
        type: "HITPAY_PAYMENT",
        reference: "{{ $reference }}",
        status: "{{ $status }}"
    };

    // If this was opened as a popup
    if (window.opener && !window.opener.closed) {
        window.opener.postMessage(payload, "*");

        // close popup
        setTimeout(() => window.close(), 800);
    } else {
        // fallback: go back to main POS directly
        setTimeout(() => {
            window.location.href = "https://smart-shop-with-barcode.shop/#/app/pos?reference={{ $reference }}&status={{ $status }}";
        }, 1200);
    }
</script>

</body>
</html>
