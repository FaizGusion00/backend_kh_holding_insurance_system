@php($title = 'Withdrawal Details')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Withdrawal Request #{{ $withdrawal->id }}</h1>
	<a href="{{ route('admin.withdrawals.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Withdrawals</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">Request Information</h3>
		<dl class="space-y-4">
			<div>
				<dt class="text-sm font-medium text-gray-500">Request ID</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->request_id ?? 'N/A' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Amount</dt>
				<dd class="mt-1 text-lg font-semibold text-green-600">RM {{ number_format(($withdrawal->amount_cents ?? 0) / 100, 2) }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Status</dt>
				<dd class="mt-1">
					<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
						{{ $withdrawal->status === 'paid' ? 'bg-green-100 text-green-800' : 
						   ($withdrawal->status === 'approved' ? 'bg-blue-100 text-blue-800' :
						   ($withdrawal->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
						{{ ucfirst($withdrawal->status) }}
					</span>
				</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Requested Date</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->created_at->format('M d, Y H:i') }}</dd>
			</div>
			@if($withdrawal->paid_at)
				<div>
					<dt class="text-sm font-medium text-gray-500">Paid Date</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->paid_at->format('M d, Y H:i') }}</dd>
				</div>
			@endif
		</dl>
	</div>

	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">User Information</h3>
		<dl class="space-y-4">
			<div>
				<dt class="text-sm font-medium text-gray-500">Name</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->user->name ?? 'Unknown' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Email</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->user->email ?? 'N/A' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Agent Code</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->user->agent_code ?? 'Not assigned' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Current Balance</dt>
				<dd class="mt-1 text-sm text-gray-900">RM {{ number_format(($withdrawal->user->agentWallet->balance_cents ?? 0) / 100, 2) }}</dd>
			</div>
		</dl>
	</div>
</div>

@if($withdrawal->bank_meta)
<div class="mt-6 bg-white rounded-lg shadow p-6">
	<h3 class="text-lg font-semibold mb-4">Bank Details</h3>
	<dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
		<div>
			<dt class="text-sm font-medium text-gray-500">Bank Name</dt>
			<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->bank_meta['bank_name'] ?? 'N/A' }}</dd>
		</div>
		<div>
			<dt class="text-sm font-medium text-gray-500">Account Number</dt>
			<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->bank_meta['account_number'] ?? 'N/A' }}</dd>
		</div>
		<div>
			<dt class="text-sm font-medium text-gray-500">Account Owner</dt>
			<dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->bank_meta['account_owner'] ?? 'N/A' }}</dd>
		</div>
	</dl>
</div>
@endif

<div class="mt-6 bg-white rounded-lg shadow p-6">
	<h3 class="text-lg font-semibold mb-4">Actions</h3>
	<div class="flex space-x-3">
		@if($withdrawal->status === 'pending')
			<form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" class="inline">
				@csrf
				<button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" 
					onclick="return confirm('Approve this withdrawal request?')">
					Approve Request
				</button>
			</form>
			<button onclick="showRejectModal()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
				Reject Request
			</button>
		@endif

		@if($withdrawal->status === 'approved')
			<form method="POST" action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}" class="inline">
				@csrf
				<button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" 
					onclick="return confirm('Mark as paid? This will deduct RM {{ number_format(($withdrawal->amount_cents ?? 0) / 100, 2) }} from the user wallet.')">
					Mark as Paid
				</button>
			</form>
		@endif

		@if($withdrawal->status === 'paid')
			<div class="text-green-600 font-medium">✓ This withdrawal has been processed</div>
		@endif

		@if($withdrawal->status === 'rejected')
			<div class="text-red-600 font-medium">✗ This withdrawal was rejected</div>
		@endif
	</div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
	<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
		<div class="mt-3">
			<h3 class="text-lg font-medium text-gray-900 mb-4">Reject Withdrawal Request</h3>
			<form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
				@csrf
				<div class="mb-4">
					<label class="block text-sm font-medium text-gray-700 mb-2">Reason (optional)</label>
					<textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2" 
						placeholder="Enter rejection reason..."></textarea>
				</div>
				<div class="flex justify-end space-x-3">
					<button type="button" onclick="closeRejectModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
					<button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject Request</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
function showRejectModal() {
	document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
	document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
