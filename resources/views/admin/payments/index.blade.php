@extends('admin.layout')
@section('title', 'Payment Verification')

@section('content')
<div class="container-fluid py-4">
    {{-- <h2 class="mb-4">Payment Verification</h2> --}}

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="{{ $payment->status === 'completed' ? 'table-success' : ($payment->status === 'rejected' ? 'table-danger' : '') }}">
                            <td><code>{{ $payment->uuid }}</code></td>
                            <td>
                                {{ $payment->user->email ?? $payment->user->phone ?? '—' }}
                                {{-- <br><small class="text-muted">ID: {{ $payment->user->id }}</small> --}}
                            </td>
                            <td>{{ $payment->plan->name ?? '—' }}</td>
                            <td><strong>৳{{ number_format($payment->amount, 2) }}</strong></td>
                            <td><code>{{ $payment->transaction_id }}</code></td>
                            <td>
                                <span class="badge bg-{{
                                    $payment->status === 'completed' ? 'success' :
                                    ($payment->status === 'rejected' ? 'danger' : 'warning')
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($payment->status === 'pending' || $payment->status === 'under_review')
                                <div class="btn-group">
                                    <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('Approve this payment?')">
                                            Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Reject this payment?')">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                                @else
                                    <span class="text-muted">Processed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
