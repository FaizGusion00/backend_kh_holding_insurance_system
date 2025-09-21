@php($title = 'Edit ' . ucfirst($type))
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Edit {{ ucfirst($type) }}: {{ $facility->name }}</h1>
	<a href="{{ route('admin.facilities.' . $type . 's.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to {{ ucfirst($type) }}s</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<form method="POST" action="{{ route('admin.facilities.' . $type . 's.update', $facility) }}">
		@csrf @method('PUT')
		<input type="hidden" name="type" value="{{ $type }}">
		
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-2">{{ ucfirst($type) }} Name</label>
				<input type="text" name="name" value="{{ old('name', $facility->name) }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
				<textarea name="address" rows="2" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $facility->address) }}</textarea>
				@error('address')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">City</label>
				<input type="text" name="city" value="{{ old('city', $facility->city) }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('city')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">State</label>
				<select name="state" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
					<option value="">Select State</option>
					<option value="Johor" {{ old('state', $facility->state) === 'Johor' ? 'selected' : '' }}>Johor</option>
					<option value="Kedah" {{ old('state', $facility->state) === 'Kedah' ? 'selected' : '' }}>Kedah</option>
					<option value="Kelantan" {{ old('state', $facility->state) === 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
					<option value="Kuala Lumpur" {{ old('state', $facility->state) === 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
					<option value="Labuan" {{ old('state', $facility->state) === 'Labuan' ? 'selected' : '' }}>Labuan</option>
					<option value="Melaka" {{ old('state', $facility->state) === 'Melaka' ? 'selected' : '' }}>Melaka</option>
					<option value="Negeri Sembilan" {{ old('state', $facility->state) === 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
					<option value="Pahang" {{ old('state', $facility->state) === 'Pahang' ? 'selected' : '' }}>Pahang</option>
					<option value="Perak" {{ old('state', $facility->state) === 'Perak' ? 'selected' : '' }}>Perak</option>
					<option value="Perlis" {{ old('state', $facility->state) === 'Perlis' ? 'selected' : '' }}>Perlis</option>
					<option value="Pulau Pinang" {{ old('state', $facility->state) === 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
					<option value="Putrajaya" {{ old('state', $facility->state) === 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
					<option value="Sabah" {{ old('state', $facility->state) === 'Sabah' ? 'selected' : '' }}>Sabah</option>
					<option value="Sarawak" {{ old('state', $facility->state) === 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
					<option value="Selangor" {{ old('state', $facility->state) === 'Selangor' ? 'selected' : '' }}>Selangor</option>
					<option value="Terengganu" {{ old('state', $facility->state) === 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
				</select>
				@error('state')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
				<input type="text" name="postal_code" value="{{ old('postal_code', $facility->postal_code) }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('postal_code')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
				<input type="text" name="phone_number" value="{{ old('phone_number', $facility->phone_number) }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('phone_number')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<div class="space-y-3">
					<div class="flex items-center">
						<input type="checkbox" name="is_panel" value="1" id="is_panel" 
							{{ old('is_panel', $facility->is_panel) ? 'checked' : '' }}
							class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
						<label for="is_panel" class="ml-2 block text-sm text-gray-900">Panel {{ ucfirst($type) }}</label>
					</div>
					
					<div class="flex items-center">
						<input type="checkbox" name="is_active" value="1" id="is_active" 
							{{ old('is_active', $facility->is_active) ? 'checked' : '' }}
							class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
						<label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-6 flex justify-end space-x-3">
			<a href="{{ route('admin.facilities.' . $type . 's.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update {{ ucfirst($type) }}</button>
		</div>
	</form>
</div>
@endsection
