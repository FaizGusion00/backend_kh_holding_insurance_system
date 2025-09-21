@extends('admin.layouts.auth')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-lg">
	<div class="text-center mb-6">
		<h1 class="text-3xl font-bold text-gray-900">KH Holdings Admin</h1>
		<p class="text-gray-600 mt-2">Sign in to your admin account</p>
	</div>
	
	<form method="POST" action="{{ route('admin.login.post') }}">
		@csrf
		<div class="mb-4">
			<label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
			<input name="email" type="email" value="{{ old('email') }}" 
				   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
				   placeholder="Enter your email" required />
			@error('email')
				<div class="text-red-600 text-sm mt-1">{{ $message }}</div>
			@enderror
		</div>
		
		<div class="mb-6">
			<label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
			<input name="password" type="password" 
				   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
				   placeholder="Enter your password" required />
			@error('password')
				<div class="text-red-600 text-sm mt-1">{{ $message }}</div>
			@enderror
		</div>
		
		<div class="flex items-center justify-between mb-6">
			<label class="inline-flex items-center">
				<input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
				<span class="ml-2 text-sm text-gray-600">Remember me</span>
			</label>
		</div>
		
		<!-- Auto-fill notification (hidden by default) -->
		<div id="auto-fill-notification" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
			<div class="flex items-center">
				<svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<span class="text-sm text-blue-800">Credentials auto-filled from saved data</span>
			</div>
		</div>
		
		<button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
			Login
		</button>
	</form>
</div>
@endsection


