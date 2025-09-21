@php($title = ucfirst($type) . ' Management')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">{{ ucfirst($type) }}s</h1>
	<div class="space-x-2">
		<a href="{{ route('admin.facilities.hospitals.index') }}" 
			class="px-4 py-2 rounded {{ $type === 'hospital' ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-700' }}">
			Hospitals
		</a>
		<a href="{{ route('admin.facilities.clinics.index') }}" 
			class="px-4 py-2 rounded {{ $type === 'clinic' ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-700' }}">
			Clinics
		</a>
		<a href="{{ route('admin.facilities.' . $type . 's.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
			Add New {{ ucfirst($type) }}
		</a>
	</div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
	<div class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				@forelse($facilities as $facility)
					<tr>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-medium text-gray-900">{{ $facility->name }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">{{ $facility->address }}</div>
							<div class="text-sm text-gray-500">{{ $facility->city }}, {{ $facility->state }} {{ $facility->postal_code }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
							{{ $facility->phone_number ?: 'No phone' }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $facility->is_panel ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
								{{ $facility->is_panel ? 'Panel' : 'Non-Panel' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
								{{ $facility->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
								{{ $facility->is_active ? 'Active' : 'Inactive' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
							<a href="{{ route('admin.facilities.' . $type . 's.show', $facility) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
							<a href="{{ route('admin.facilities.' . $type . 's.edit', $facility) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
							<form method="POST" action="{{ route('admin.facilities.' . $type . 's.destroy', $facility) }}" class="inline" 
								onsubmit="return confirm('Are you sure?')">
								@csrf @method('DELETE')
								<button class="text-red-600 hover:text-red-900">Delete</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="6" class="px-6 py-4 text-center text-gray-500">No {{ $type }}s found</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="mt-6">
	{{ $facilities->links() }}
</div>
@endsection
