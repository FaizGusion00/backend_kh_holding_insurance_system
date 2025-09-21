@php($title = 'Create ' . ucfirst($type))
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Create New {{ ucfirst($type) }}</h1>
	<a href="{{ route('admin.facilities.' . $type . 's.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to {{ ucfirst($type) }}s</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<form method="POST" action="{{ route('admin.facilities.' . $type . 's.store') }}">
		@csrf
		<input type="hidden" name="type" value="{{ $type }}">
		
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-2">{{ ucfirst($type) }} Name</label>
				<input type="text" name="name" value="{{ old('name') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
				<textarea name="address" rows="2" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
				@error('address')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">City</label>
				<input type="text" name="city" value="{{ old('city') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('city')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">State</label>
				<select name="state" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
					<option value="">Select State</option>
					<option value="Johor" {{ old('state') === 'Johor' ? 'selected' : '' }}>Johor</option>
					<option value="Kedah" {{ old('state') === 'Kedah' ? 'selected' : '' }}>Kedah</option>
					<option value="Kelantan" {{ old('state') === 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
					<option value="Kuala Lumpur" {{ old('state') === 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
					<option value="Labuan" {{ old('state') === 'Labuan' ? 'selected' : '' }}>Labuan</option>
					<option value="Melaka" {{ old('state') === 'Melaka' ? 'selected' : '' }}>Melaka</option>
					<option value="Negeri Sembilan" {{ old('state') === 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
					<option value="Pahang" {{ old('state') === 'Pahang' ? 'selected' : '' }}>Pahang</option>
					<option value="Perak" {{ old('state') === 'Perak' ? 'selected' : '' }}>Perak</option>
					<option value="Perlis" {{ old('state') === 'Perlis' ? 'selected' : '' }}>Perlis</option>
					<option value="Pulau Pinang" {{ old('state') === 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
					<option value="Putrajaya" {{ old('state') === 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
					<option value="Sabah" {{ old('state') === 'Sabah' ? 'selected' : '' }}>Sabah</option>
					<option value="Sarawak" {{ old('state') === 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
					<option value="Selangor" {{ old('state') === 'Selangor' ? 'selected' : '' }}>Selangor</option>
					<option value="Terengganu" {{ old('state') === 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
				</select>
				@error('state')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
				<input type="text" name="postal_code" value="{{ old('postal_code') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('postal_code')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
				<input type="text" name="phone_number" value="{{ old('phone_number') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('phone_number')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<div class="space-y-3">
					<div class="flex items-center">
						<input type="checkbox" name="is_panel" value="1" id="is_panel" 
							{{ old('is_panel', true) ? 'checked' : '' }}
							class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
						<label for="is_panel" class="ml-2 block text-sm text-gray-900">Panel {{ ucfirst($type) }}</label>
					</div>
					
					<div class="flex items-center">
						<input type="checkbox" name="is_active" value="1" id="is_active" 
							{{ old('is_active', true) ? 'checked' : '' }}
							class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
						<label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-6 flex justify-end space-x-3">
			<a href="{{ route('admin.facilities.' . $type . 's.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create {{ ucfirst($type) }}</button>
		</div>
	</form>
</div>
@endsection
