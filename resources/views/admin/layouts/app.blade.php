<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - {{ $title ?? 'Dashboard' }}</title>
	@vite(['resources/css/app.css','resources/js/app.js'])
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
	<div class="flex">
		<aside class="hidden md:block w-72 bg-white border-r border-gray-200 min-h-screen">
			<div class="p-6 border-b">
				<a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold">KH Holdings Admin</a>
			</div>
			<nav class="p-4 space-y-1">
				<a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Dashboard</a>
				<a href="{{ route('admin.plans.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Plans</a>
				<a href="{{ route('admin.users.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Users</a>
				<a href="{{ route('admin.commissions.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Commissions</a>
				<a href="{{ route('admin.withdrawals.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Withdrawals</a>
				<a href="{{ route('admin.facilities.hospitals.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Hospitals</a>
				<a href="{{ route('admin.facilities.clinics.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">Clinics</a>
			</nav>
		</aside>
		<main class="flex-1">
			<header class="sticky top-0 bg-white border-b">
				<div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
					<div class="font-semibold">{{ $title ?? '' }}</div>
					<form method="POST" action="{{ route('admin.logout') }}">
						@csrf
						<button class="text-red-600 hover:text-red-700">Logout</button>
					</form>
				</div>
			</header>
			<div class="max-w-7xl mx-auto p-4">
				@if(session('success'))
					<div class="mb-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
				@endif
				@yield('content')
			</div>
		</main>
	</div>
</body>
</html>


