@php($title = 'User Network')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Network: {{ $user->name }} ({{ $user->agent_code }})</h1>
	<a href="{{ route('admin.users.show', $user) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to User</a>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
	<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
		<div class="text-center">
			<div class="text-2xl font-bold text-blue-600">{{ $network->total() }}</div>
			<div class="text-sm text-gray-500">Total Network Members</div>
		</div>
		<div class="text-center">
			<div class="text-2xl font-bold text-green-600">{{ $user->referredUsers->count() }}</div>
			<div class="text-sm text-gray-500">Direct Referrals</div>
		</div>
		<div class="text-center">
			<div class="text-2xl font-bold text-yellow-600">RM {{ number_format(($user->agentWallet->balance_cents ?? 0) / 100, 2) }}</div>
			<div class="text-sm text-gray-500">Current Balance</div>
		</div>
	</div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="p-6 border-b">
		<h3 class="text-lg font-semibold">Network Members</h3>
	</div>
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent Code</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policies</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($network as $member)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="flex items-center">
								<div class="flex-shrink-0 h-10 w-10">
									<div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
										<span class="text-sm font-medium text-gray-700">{{ substr($member->name, 0, 1) }}</span>
									</div>
								</div>
								<div class="ml-4">
									<div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
									<div class="text-sm text-gray-500">ID: {{ $member->id }}</div>
								</div>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">{{ $member->email }}</div>
							<div class="text-sm text-gray-500">{{ $member->phone_number ?: 'No phone' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $member->agent_code ?: 'Not assigned' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							RM {{ number_format(($member->agentWallet->balance_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $member->memberPolicies->count() }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
							{{ $member->created_at->format('M d, Y') }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
							<a href="{{ route('admin.users.show', $member) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
							@if($member->agent_code)
								<a href="{{ route('admin.users.network', $member) }}" class="text-green-600 hover:text-green-900">Network</a>
							@endif
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="7" class="px-6 py-4 text-center text-gray-500">No network members found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $network->links() }}
</div>
@endsection
