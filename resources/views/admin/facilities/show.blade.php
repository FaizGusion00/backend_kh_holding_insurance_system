@php($title = ucfirst($type) . ' Details')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">{{ ucfirst($type) }}: {{ $facility->name }}</h1>
	<div class="space-x-2">
		<a href="{{ route('admin.facilities.' . $type . 's.edit', $facility) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
		<a href="{{ route('admin.facilities.' . $type . 's.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to {{ ucfirst($type) }}s</a>
	</div>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
		<div>
			<h3 class="text-lg font-semibold mb-4">{{ ucfirst($type) }} Information</h3>
			<dl class="space-y-4">
				<div>
					<dt class="text-sm font-medium text-gray-500">Name</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->name }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Address</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->address ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">City</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->city ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">State</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->state ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Postal Code</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->postal_code ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Phone Number</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->phone_number ?: 'Not provided' }}</dd>
				</div>
			</dl>
		</div>

		<div>
			<h3 class="text-lg font-semibold mb-4">Status & Settings</h3>
			<dl class="space-y-4">
				<div>
					<dt class="text-sm font-medium text-gray-500">Panel Status</dt>
					<dd class="mt-1">
						<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
							{{ $facility->is_panel ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
							{{ $facility->is_panel ? 'Panel' : 'Non-Panel' }}
						</span>
					</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Active Status</dt>
					<dd class="mt-1">
						<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
							{{ $facility->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
							{{ $facility->is_active ? 'Active' : 'Inactive' }}
						</span>
					</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Created</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->created_at->format('M d, Y H:i') }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Last Updated</dt>
					<dd class="mt-1 text-sm text-gray-900">{{ $facility->updated_at->format('M d, Y H:i') }}</dd>
				</div>
			</dl>
		</div>
	</div>

	@if($facility->address && $facility->city && $facility->state)
	<div class="mt-6 pt-6 border-t">
		<h3 class="text-lg font-semibold mb-4">Full Address</h3>
		<div class="bg-gray-50 p-4 rounded-lg">
			<p class="text-sm text-gray-900">
				{{ $facility->name }}<br>
				{{ $facility->address }}<br>
				{{ $facility->city }}, {{ $facility->state }} {{ $facility->postal_code }}<br>
				@if($facility->phone_number)
					Tel: {{ $facility->phone_number }}
				@endif
			</p>
		</div>
	</div>
	@endif
</div>
@endsection
