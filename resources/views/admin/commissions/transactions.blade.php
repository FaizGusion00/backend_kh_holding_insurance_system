@php($title = 'Commission Transactions')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">All Commission Transactions</h1>
	<a href="{{ route('admin.commissions.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Overview</a>
</div>

<div class="bg-white rounded-lg shadow mb-6 p-6">
	<form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
			<select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
				<option value="">All Statuses</option>
				<option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
				<option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted</option>
				<option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>Reversed</option>
			</select>
		</div>
		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
			<select name="level" class="w-full border border-gray-300 rounded-md px-3 py-2">
				<option value="">All Levels</option>
				@for($i = 1; $i <= 5; $i++)
					<option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
				@endfor
			</select>
		</div>
		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
			<input type="date" name="from_date" value="{{ request('from_date') }}" 
				class="w-full border border-gray-300 rounded-md px-3 py-2">
		</div>
		<div class="flex items-end">
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Filter</button>
		</div>
	</form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Earner</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basis</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($transactions as $transaction)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->id }}</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-medium text-gray-900">{{ $transaction->earner->name ?? 'Unknown' }}</div>
							<div class="text-sm text-gray-500">{{ $transaction->earner->agent_code ?? 'N/A' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">{{ $transaction->source->name ?? 'Unknown' }}</div>
							<div class="text-sm text-gray-500">{{ $transaction->source->agent_code ?? 'N/A' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
								L{{ $transaction->level }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $transaction->plan->name ?? 'Plan #' . $transaction->plan_id }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							RM {{ number_format(($transaction->basis_amount_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
							RM {{ number_format(($transaction->commission_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $transaction->status === 'posted' ? 'bg-green-100 text-green-800' : 
								   ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
								{{ ucfirst($transaction->status) }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
							{{ $transaction->created_at->format('M d, Y H:i') }}
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="9" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $transactions->links() }}
</div>
@endsection
