@php($title = 'Users Management')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Users</h1>
	<a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add New User</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent Code</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrer</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($users as $user)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="flex items-center">
								<div class="flex-shrink-0 h-10 w-10">
									<div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
										<span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
									</div>
								</div>
								<div class="ml-4">
									<div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
									<div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
								</div>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">{{ $user->email }}</div>
							<div class="text-sm text-gray-500">{{ $user->phone_number ?: 'No phone' }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $user->agent_code ?: 'Not assigned' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $user->referrer_code ?: 'None' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							RM {{ number_format(($user->agentWallet->balance_cents ?? 0) / 100, 2) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
							{{ $user->created_at->format('M d, Y') }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
							<a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
							<a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
							@if($user->agent_code)
								<a href="{{ route('admin.users.network', $user) }}" class="text-green-600 hover:text-green-900">Network</a>
							@endif
							<a href="{{ route('admin.users.medical', $user) }}" class="text-blue-600 hover:text-blue-900">Medical Info</a>
							<form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" 
								onsubmit="return confirm('Are you sure?')">
								@csrf @method('DELETE')
								<button class="text-red-600 hover:text-red-900">Delete</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $users->links() }}
</div>
@endsection
