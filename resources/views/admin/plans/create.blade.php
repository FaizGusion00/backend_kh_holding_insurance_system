@php($title = 'Create Plan')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Create New Plan</h1>
	<a href="{{ route('admin.plans.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Plans</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<form method="POST" action="{{ route('admin.plans.store') }}">
		@csrf
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Plan Name</label>
				<input type="text" name="name" value="{{ old('name') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
				<input type="text" name="slug" value="{{ old('slug') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('slug')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
				<textarea name="description" rows="3" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
				@error('description')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Price (cents)</label>
				<input type="number" name="price_cents" value="{{ old('price_cents') }}" min="0"
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('price_cents')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
				<p class="text-sm text-gray-500 mt-1">Enter amount in cents (e.g., 1000 = RM10.00)</p>
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Commission Type</label>
				<select name="uses_percentage_commission" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
					<option value="1" {{ old('uses_percentage_commission') == '1' ? 'selected' : '' }}>Percentage</option>
					<option value="0" {{ old('uses_percentage_commission') == '0' ? 'selected' : '' }}>Fixed Amount</option>
				</select>
				@error('uses_percentage_commission')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div class="md:col-span-2">
				<div class="flex items-center">
					<input type="checkbox" name="active" value="1" id="active" 
						{{ old('active', true) ? 'checked' : '' }}
						class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
					<label for="active" class="ml-2 block text-sm text-gray-900">Active</label>
				</div>
				@error('active')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>
		</div>

		<div class="mt-6 flex justify-end space-x-3">
			<a href="{{ route('admin.plans.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create Plan</button>
		</div>
	</form>
</div>
@endsection
