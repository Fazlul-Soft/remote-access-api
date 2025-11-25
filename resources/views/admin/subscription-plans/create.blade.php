{{-- Use this for both create.blade.php and edit.blade.php --}}
@extends('admin.layout')
@section('title', isset($subscriptionPlan) ? 'Edit Plan' : 'Create Plan')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">

@section('content')
    <h3>{{ isset($subscriptionPlan) ? 'Edit' : 'Create' }} Subscription Plan</h3>

    <form
        action="{{ isset($subscriptionPlan)
            ? route('admin.subscription-plans.update', $subscriptionPlan)
            : route('admin.subscription-plans.store') }}"
        method="POST">
        @csrf
        @if (isset($subscriptionPlan))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Plan Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $subscriptionPlan->name ?? '') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <div id="quill-editor" style="height: 300px;">
                                {!! old('description', $subscriptionPlan->description ?? '') !!}
                            </div>
                            <input type="hidden" name="description" id="quill-hidden-input">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Price ($)</label>
                                <input type="number" step="0.01" name="price" class="form-control"
                                    value="{{ old('price', $subscriptionPlan->price ?? '') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Max Devices</label>
                                <input type="number" name="max_devices" class="form-control"
                                    value="{{ old('max_devices', $subscriptionPlan->max_devices ?? '') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Duration (months)</label>
                                <input type="number" name="duration" class="form-control"
                                    value="{{ old('duration', $subscriptionPlan->duration ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Grace Period (days)</label>
                                <input type="number" name="grace_period_days" class="form-control"
                                    value="{{ old('grace_period_days', $subscriptionPlan->grace_period_days ?? '') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Hide Data After (days)</label>
                            <input type="number" name="hide_data_after_days" class="form-control"
                                value="{{ old('hide_data_after_days', $subscriptionPlan->hide_data_after_days ?? '') }}">
                        </div>

                        <button type="submit" class="btn btn-success btn-lg">
                            {{ isset($subscriptionPlan) ? 'Update' : 'Create' }} Plan
                        </button>
                        <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Quill JS (Free CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        const quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        header: [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'image'],
                    [{
                        list: 'ordered'
                    }, {
                        list: 'bullet'
                    }],
                    ['clean']
                ]
            },
        });

        // Sync Quill content to hidden input before submit
        const form = document.querySelector('form');
        form.onsubmit = function() {
            document.querySelector('#quill-hidden-input').value = quill.root.innerHTML;
        };

        // Optional: Load existing content (for edit page)
        @if (isset($subscriptionPlan))
            quill.root.innerHTML = `{!! addslashes($subscriptionPlan->description ?? '') !!}`;
        @endif
    </script>

    <style>
        .ql-editor {
            min-height: 200px;
            font-size: 1rem;
        }
    </style>
@endsection
