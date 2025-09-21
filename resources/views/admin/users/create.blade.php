@php($title = 'Create User')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Create New User</h1>
	<a href="{{ route('admin.users.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Users</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<form method="POST" action="{{ route('admin.users.store') }}">
		@csrf
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
				<input type="text" name="name" value="{{ old('name') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
				<input type="email" name="email" value="{{ old('email') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
				<input type="text" name="phone" value="{{ old('phone') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
				@error('phone')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Referrer Code</label>
				<input type="text" name="referrer_code" value="{{ old('referrer_code') }}" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
					placeholder="Enter existing agent code">
				@error('referrer_code')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
				<input type="password" name="password" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
				@error('password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
				<input type="password" name="password_confirmation" 
					class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
			</div>
		</div>

		<div class="mt-6 flex justify-end space-x-3">
			<a href="{{ route('admin.users.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create User</button>
		</div>
	</form>
</div>
@endsection
