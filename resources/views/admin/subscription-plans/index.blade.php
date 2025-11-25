@extends('admin.layout')
@section('title', 'Subscription Plans')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Subscription Plans</h3>
        <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">
            + Add New Plan
        </a>
    </div>

    <div class="row">
        @foreach ($plans as $plan)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-{{ $loop->first ? 'primary' : 'secondary' }} text-white">
                        <h5 class="mb-0">{{ $plan->name }}</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="text-primary">${{ number_format($plan->price, 2) }}
                            @if ($plan->duration)
                                / {{ $plan->duration }} month{{ $plan->duration > 1 ? 's' : '' }}
                            @endif
                        </h4>
                        <p><strong>Max Devices:</strong> {{ $plan->max_devices }}</p>
                        <div class="mb-3">{!! Str::limit($plan->description, 150) !!}</div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex w-100 gap-2">
                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                class="btn btn-sm btn-warning flex-fill">Edit</a>

                            <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST"
                                class="flex-fill">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100"
                                    onclick="return confirm('Delete this plan?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
@endsection
