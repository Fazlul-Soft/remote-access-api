@extends('admin.layout')
{{-- @section('title', 'Payment Methods') --}}

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Payment Methods</h3>
        <a href="{{ route('admin.payment-method-create') }}" class="btn btn-primary">
            + Add New Method
        </a>
    </div>

    <div class="row">
        @isset($paymentMethods)
            @foreach ($paymentMethods as $plan)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $plan->payment_method }}</h5>
                        </div>
                        <div class="card-body text-center">
                            @if ($plan->logo)
                                <img src="{{ asset('uploads/' . $plan->logo) }}" alt="{{ $plan->payment_method }}"
                                    class="img-fluid rounded mb-3" style="max-height: 100px;">
                            @else
                                <div class="bg-light border rounded d-inline-block p-5 mb-3">
                                    <i class="fas fa-credit-card fa-3x text-muted"></i>
                                </div>
                            @endif>

                            <p><strong>Merchant No:</strong> {{ $plan->merchant_no }}</p>
                            <div class="text-muted small">{!! Str::limit($plan->details, 120) !!}</div>
                            @if ($plan->note)
                                <p class="text-muted"><em>{{ $plan->note }}</em></p>
                            @endif>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.payment-method-edit', $plan) }}"
                                    class="btn btn-sm btn-warning flex-fill">Edit</a>

                                <form action="{{ route('admin.payment-method-delete', $plan) }}" method="POST"
                                    onsubmit="return confirm('Delete this payment method?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger flex-fill">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endisset

    </div>
@endsection
