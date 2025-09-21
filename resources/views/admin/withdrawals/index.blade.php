@php($title = 'Withdrawal Management')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Withdrawal Requests</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Pending</p>
				<p class="text-2xl font-semibold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Approved</p>
				<p class="text-2xl font-semibold text-blue-600">{{ $stats['approved'] ?? 0 }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Paid</p>
				<p class="text-2xl font-semibold text-green-600">{{ $stats['paid'] ?? 0 }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Rejected</p>
				<p class="text-2xl font-semibold text-red-600">{{ $stats['rejected'] ?? 0 }}</p>
			</div>
		</div>
	</div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($withdrawals as $withdrawal)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="flex items-center">
								<div class="flex-shrink-0 h-10 w-10">
									<div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
										<span class="text-sm font-medium text-gray-700">{{ substr($withdrawal->user->name ?? 'U', 0, 1) }}</span>
									</div>
								</div>
								<div class="ml-4">
									<div class="text-sm font-medium text-gray-900">{{ $withdrawal->user->name ?? 'Unknown' }}</div>
									<div class="text-sm text-gray-500">{{ $withdrawal->user->email ?? 'N/A' }}</div>
								</div>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-semibold text-gray-900">RM {{ number_format(($withdrawal->amount_cents ?? 0) / 100, 2) }}</div>
							@if($withdrawal->request_id)
								<div class="text-xs text-gray-500">{{ $withdrawal->request_id }}</div>
							@endif
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							@if($withdrawal->bank_meta)
								<div class="text-sm text-gray-900">{{ $withdrawal->bank_meta['bank_name'] ?? 'N/A' }}</div>
								<div class="text-xs text-gray-500">{{ $withdrawal->bank_meta['account_number'] ?? 'N/A' }}</div>
								<div class="text-xs text-gray-500">{{ $withdrawal->bank_meta['account_owner'] ?? 'N/A' }}</div>
							@else
								<div class="text-sm text-gray-500">No bank details</div>
							@endif
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $withdrawal->status === 'paid' ? 'bg-green-100 text-green-800' : 
								   ($withdrawal->status === 'approved' ? 'bg-blue-100 text-blue-800' :
								   ($withdrawal->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
								{{ ucfirst($withdrawal->status) }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
							{{ $withdrawal->created_at->format('M d, Y H:i') }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
							<a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
							@if($withdrawal->status === 'pending')
								<form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" class="inline">
									@csrf
									<button class="text-green-600 hover:text-green-900" onclick="return confirm('Approve this withdrawal?')">Approve</button>
								</form>
								<button onclick="showRejectModal({{ $withdrawal->id }})" class="text-red-600 hover:text-red-900">Reject</button>
							@endif
							@if($withdrawal->status === 'approved')
								<form method="POST" action="{{ route('admin.withdrawals.mark-paid', $withdrawal) }}" class="inline">
									@csrf
									<button class="text-blue-600 hover:text-blue-900" onclick="return confirm('Mark as paid? This will deduct from user wallet.')">Mark Paid</button>
								</form>
							@endif
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="6" class="px-6 py-4 text-center text-gray-500">No withdrawal requests found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $withdrawals->links() }}
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
	<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
		<div class="mt-3">
			<h3 class="text-lg font-medium text-gray-900 mb-4">Reject Withdrawal</h3>
			<form id="rejectForm" method="POST">
				@csrf
				<div class="mb-4">
					<label class="block text-sm font-medium text-gray-700 mb-2">Reason (optional)</label>
					<textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="Enter rejection reason..."></textarea>
				</div>
				<div class="flex justify-end space-x-3">
					<button type="button" onclick="closeRejectModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
					<button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
function showRejectModal(withdrawalId) {
	document.getElementById('rejectForm').action = '/admin/withdrawals/' + withdrawalId + '/reject';
	document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
	document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
