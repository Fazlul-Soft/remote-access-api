@extends('admin.layout')

@section('title', isset($paymentDetails) ? 'Edit Payment Method' : 'Create Payment Method')

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

@section('content')
    <div class="container-fluid">
        <form
            action="{{ isset($paymentDetails)
                ? route('admin.payment-method-update', $paymentDetails)
                : route('admin.payment-method-add') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            {{-- @if (isset($paymentDetails))
                @method('POST')
            @endif --}}

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Method Name <span class="text-danger">*</span></label>
                                <input type="text" name="payment_method" class="form-control"
                                    value="{{ old('payment_method', $paymentDetails->payment_method ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Merchant No <span class="text-danger">*</span></label>
                                <input type="text" name="merchant_no" class="form-control"
                                    value="{{ old('merchant_no', $paymentDetails->merchant_no ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Details</label>
                                <textarea name="details" class="form-control">{{ old('details', $paymentDetails->details ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <input type="text" name="note" class="form-control"
                                    value="{{ old('note', $paymentDetails->note ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Logo</label>
                                @if (isset($paymentDetails) && $paymentDetails->logo)
                                    <div class="mb-2">
                                        <img src="{{ $paymentDetails->logo_url }}" alt="Current Logo"
                                            style="max-height: 80px; border-radius: 8px;">
                                        <small class="text-muted d-block">Current logo</small>
                                    </div>
                                @endif
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Max 2MB. Allowed: jpeg, png, jpg, gif, webp</small>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">
                                    {{ isset($paymentDetails) ? 'Update' : 'Create' }} Method
                                </button>
                                <a href="{{ route('admin.payment-methods') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
