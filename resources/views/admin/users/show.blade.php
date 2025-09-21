@php($title = 'User Details')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">User: {{ $user->name }}</h1>
	<div class="space-x-2">
		<a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
		@if($user->agent_code)
			<a href="{{ route('admin.users.network', $user) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">View Network</a>
		@endif
		<a href="{{ route('admin.users.medical', $user) }}" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 font-medium">Medical Info</a>
		<a href="{{ route('admin.users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Users</a>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">User Information</h3>
		<dl class="space-y-4">
			<div>
				<dt class="text-sm font-medium text-gray-500">Name</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Email</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Phone</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->phone_number ?: 'Not provided' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Agent Code</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->agent_code ?: 'Not assigned' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Referrer Code</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->referrer_code ?: 'None' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Joined</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Last Updated</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</dd>
			</div>
		</dl>
	</div>

	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">Wallet & Activity</h3>
		<dl class="space-y-4">
			<div>
				<dt class="text-sm font-medium text-gray-500">Current Balance</dt>
				<dd class="mt-1 text-lg font-semibold text-green-600">
					RM {{ number_format(($user->agentWallet->balance_cents ?? 0) / 100, 2) }}
				</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Total Policies</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->memberPolicies->count() }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Direct Referrals</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $user->referredUsers->count() }}</dd>
			</div>
		</dl>
	</div>
</div>

@if($user->memberPolicies->count() > 0)
<div class="mt-6 bg-white rounded-lg shadow">
	<div class="p-6 border-b">
		<h3 class="text-lg font-semibold">Policies</h3>
	</div>
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Policy Number</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-gray-200">
				@foreach($user->memberPolicies as $policy)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $policy->policy_number }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Plan ID: {{ $policy->plan_id }}</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $policy->status === 'active' ? 'bg-green-100 text-green-800' : 
								   ($policy->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
								{{ ucfirst($policy->status) }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $policy->start_date }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $policy->end_date }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endif

@if($user->referredUsers->count() > 0)
<div class="mt-6 bg-white rounded-lg shadow">
	<div class="p-6 border-b">
		<h3 class="text-lg font-semibold">Direct Referrals</h3>
	</div>
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent Code</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-gray-200">
				@foreach($user->referredUsers as $referral)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $referral->name }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $referral->email }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $referral->agent_code ?: 'Not assigned' }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $referral->created_at->format('M d, Y') }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
							<a href="{{ route('admin.users.show', $referral) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endif
@endsection
