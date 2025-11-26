@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1 class="text-success mb-3">Payment Successful</h1>

    <p>Your payment has been confirmed.</p>
    <p><strong>Reference:</strong> {{ $reference }}</p>

    <a href="https://smart-shop-with-barcode.shop/#/app/pos" class="btn btn-primary mt-3">Back to POS</a>
</div>
@endsection
