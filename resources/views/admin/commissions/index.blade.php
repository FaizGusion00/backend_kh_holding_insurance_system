@php($title = 'Commission Management')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Commission Overview</h1>
	<a href="{{ route('admin.commissions.transactions') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">View All Transactions</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Total Commissions</p>
				<p class="text-2xl font-semibold text-green-600">RM {{ number_format($totalCommissions, 2) }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">This Month</p>
				<p class="text-2xl font-semibold text-blue-600">RM {{ number_format($monthlyCommissions, 2) }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Total Transactions</p>
				<p class="text-2xl font-semibold text-yellow-600">{{ $commissions->total() }}</p>
			</div>
		</div>
	</div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="p-6 border-b">
		<h3 class="text-lg font-semibold">Recent Commission Transactions</h3>
	</div>
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Earner</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basis Amount</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($commissions as $commission)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-medium text-gray-900">{{ $commission->earner->name ?? 'Unknown' }}</div>
							<div class="text-sm text-gray-500">{{ $commission->earner->email ?? 'N/A' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">{{ $commission->source->name ?? 'Unknown' }}</div>
							<div class="text-sm text-gray-500">{{ $commission->source->email ?? 'N/A' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
								L{{ $commission->level }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $commission->plan->name ?? 'Plan #' . $commission->plan_id }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							RM {{ number_format(($commission->basis_amount_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
							RM {{ number_format(($commission->commission_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $commission->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
								{{ ucfirst($commission->status) }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
							{{ $commission->created_at->format('M d, Y H:i') }}
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="8" class="px-6 py-4 text-center text-gray-500">No commission transactions found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $commissions->links() }}
</div>
@endsection
