@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <h1 class="text-danger mb-3">Payment Failed</h1>

    @if($reference)
        <p><strong>Reference:</strong> {{ $reference }}</p>
    @endif

    <p>We could not confirm your payment.</p>

    <a href="/pos" class="btn btn-secondary mt-3">Back to POS</a>
</div>
@endsection
