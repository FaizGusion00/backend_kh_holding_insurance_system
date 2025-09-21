@php($title = 'Plans Management')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Insurance Plans</h1>
	<a href="{{ route('admin.plans.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add New Plan</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission Type</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($plans as $plan)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-medium text-gray-900">{{ $plan->name }}</div>
							@if($plan->description)
								<div class="text-sm text-gray-500">{{ Str::limit($plan->description, 50) }}</div>
							@endif
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $plan->slug }}</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $plan->price_cents ? 'RM ' . number_format($plan->price_cents / 100, 2) : 'N/A' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $plan->uses_percentage_commission ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
								{{ $plan->uses_percentage_commission ? 'Percentage' : 'Fixed' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $plan->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
								{{ $plan->active ? 'Active' : 'Inactive' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
							<a href="{{ route('admin.plans.show', $plan) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
							<a href="{{ route('admin.plans.edit', $plan) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
							<a href="{{ route('admin.plans.commission-rates', $plan) }}" class="text-blue-600 hover:text-blue-900">Rates</a>
							<form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="inline" 
								onsubmit="return confirm('Are you sure?')">
								@csrf @method('DELETE')
								<button class="text-red-600 hover:text-red-900">Delete</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="6" class="px-6 py-4 text-center text-gray-500">No plans found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $plans->links() }}
</div>
@endsection
