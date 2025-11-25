@extends('admin.layout')

@section('title', 'Edit Plan: ' . $subscriptionPlan->name)

{{-- Quill CSS – put in layout or here --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Edit Subscription Plan</h2>

            <form action="{{ route('admin.subscription-plans.update', $subscriptionPlan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-9">
                        <div class="card shadow-sm">
                            <div class="card-body">

                                <!-- Plan Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $subscriptionPlan->name) }}" required>
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Rich Text Description -->
                                <div class="mb-4">
                                    <label class="form-label">Description</label>
                                    <div id="quill-editor" style="height: 320px;">
                                        <!-- Quill will fill this -->
                                    </div>
                                    <input type="hidden" name="description" id="quill-hidden-input">
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- Price -->
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="price" id="price"
                                               class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', $subscriptionPlan->price) }}" required>
                                        @error('price')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Max Devices -->
                                    <div class="col-md-6 mb-3">
                                        <label for="max_devices" class="form-label">Max Devices <span class="text-danger">*</span></label>
                                        <input type="number" name="max_devices" id="max_devices"
                                               class="form-control @error('max_devices') is-invalid @enderror"
                                               value="{{ old('max_devices', $subscriptionPlan->max_devices) }}" required>
                                        @error('max_devices')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Duration (months) -->
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label">Duration (months)</label>
                                        <input type="number" name="duration" id="duration" class="form-control"
                                               value="{{ old('duration', $subscriptionPlan->duration) }}"
                                               placeholder="Leave blank for lifetime/one-time">
                                    </div>

                                    <!-- Grace Period -->
                                    <div class="col-md-6 mb-3">
                                        <label for="grace_period_days" class="form-label">Grace Period (days)</label>
                                        <input type="number" name="grace_period_days" id="grace_period_days" class="form-control"
                                               value="{{ old('grace_period_days', $subscriptionPlan->grace_period_days) }}">
                                    </div>
                                </div>

                                <!-- Hide Data After -->
                                <div class="mb-4">
                                    <label for="hide_data_after_days" class="form-label">Hide Data After (days)</label>
                                    <input type="number" name="hide_data_after_days" id="hide_data_after_days" class="form-control"
                                           value="{{ old('hide_data_after_days', $subscriptionPlan->hide_data_after_days) }}"
                                           placeholder="e.g., 30, 90, or leave blank for never">
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-success btn-lg px-5">
                                        Update Plan
                                    </button>
                                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary btn-lg">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Preview</h6>
                            </div>
                            <div class="card-body">
                                <h5>{{ old('name', $subscriptionPlan->name) }}</h5>
                                <p class="text-muted small">{!! Str::limit($subscriptionPlan->description, 120) !!}</p>
                                <hr>
                                <p><strong>Price:</strong> ${{ number_format(old('price', $subscriptionPlan->price), 2) }}</p>
                                <p><strong>Max Devices:</strong> {{ old('max_devices', $subscriptionPlan->max_devices) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quill JS + Safe Description Loading --}}
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    // Initialize Quill
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        },
    });

    // Load existing description safely
    @if($subscriptionPlan->description)
        quill.root.innerHTML = @json($subscriptionPlan->description);
    @endif

    // Sync content to hidden input before submit
    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('quill-hidden-input').value = quill.root.innerHTML;
    });
</script>

<style>
    .ql-editor {
        min-height: 220px;
        font-size: 1rem;
    }
    .ql-container {
        font-size: 1rem;
    }
</style>
@endsection
