@php($title = 'Medical Information - ' . $user->name)
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Medical Information: {{ $user->name }}</h1>
	<div class="space-x-2">
		<a href="{{ route('admin.users.show', $user) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to User</a>
		<a href="{{ route('admin.users.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">All Users</a>
	</div>
</div>

@if($medicalInfo['has_medical_plan'])
	<!-- Policy Status Overview -->
	<div class="bg-white rounded-lg shadow p-6 mb-6">
		<div class="flex items-center justify-between mb-4">
			<h2 class="text-xl font-semibold">Policy Status Overview</h2>
			<div class="flex space-x-2">
				@if($medicalInfo['is_active'])
					<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Active</span>
				@elseif($medicalInfo['is_expired'])
					<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">Expired</span>
				@elseif($medicalInfo['is_pending'])
					<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Pending</span>
				@else
					<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">Unknown</span>
				@endif
			</div>
		</div>
		
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Plan Type</h3>
				<p class="text-lg font-semibold text-gray-900">{{ $medicalInfo['plan_name'] ?: 'Not specified' }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Payment Mode</h3>
				<p class="text-lg font-semibold text-gray-900">{{ ucfirst($medicalInfo['payment_mode'] ?: 'Not specified') }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Premium Amount</h3>
				<p class="text-lg font-semibold text-gray-900">RM {{ number_format($medicalInfo['premium_amount'] ?: 0, 2) }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Medical Card Type</h3>
				<p class="text-lg font-semibold text-gray-900">{{ $medicalInfo['medical_card_type'] ?: 'Not specified' }}</p>
			</div>
		</div>
	</div>

	<!-- Policy Dates & Duration -->
	<div class="bg-white rounded-lg shadow p-6 mb-6">
		<h2 class="text-xl font-semibold mb-4">Policy Timeline</h2>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Start Date</h3>
				<p class="text-lg font-semibold text-gray-900">
					{{ $medicalInfo['policy_start_date'] ? \Carbon\Carbon::parse($medicalInfo['policy_start_date'])->format('M d, Y') : 'Not set' }}
				</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">End Date</h3>
				<p class="text-lg font-semibold text-gray-900">
					{{ $medicalInfo['policy_end_date'] ? \Carbon\Carbon::parse($medicalInfo['policy_end_date'])->format('M d, Y') : 'Not set' }}
				</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Next Payment Due</h3>
				<p class="text-lg font-semibold text-gray-900">
					{{ $medicalInfo['next_payment_due'] ? \Carbon\Carbon::parse($medicalInfo['next_payment_due'])->format('M d, Y') : 'Not set' }}
				</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Days Remaining</h3>
				<p class="text-lg font-semibold {{ $medicalInfo['days_remaining'] < 0 ? 'text-red-600' : ($medicalInfo['days_remaining'] < 30 ? 'text-yellow-600' : 'text-green-600') }}">
					{{ $medicalInfo['days_remaining'] !== null ? $medicalInfo['days_remaining'] . ' days' : 'Not calculated' }}
				</p>
			</div>
		</div>
	</div>

	<!-- Registration Data -->
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
		<!-- Personal Information -->
		<div class="bg-white rounded-lg shadow p-6">
			<h2 class="text-xl font-semibold mb-4">Personal Information</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-sm font-medium text-gray-500">Full Name</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['full_name'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">NRIC Number</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['nric'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Race</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['race'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Height (cm)</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['height_cm'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Weight (kg)</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['weight_kg'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Phone Number</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['phone_number'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Email</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['email'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['date_of_birth'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Gender</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['gender'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Occupation</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['personal_info']['occupation'] ?: 'Not provided' }}</dd>
				</div>
			</dl>
		</div>

		<!-- Address Information -->
		<div class="bg-white rounded-lg shadow p-6">
			<h2 class="text-xl font-semibold mb-4">Address Information</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-sm font-medium text-gray-500">Address</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['address_info']['address'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">City</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['address_info']['city'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">State</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['address_info']['state'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Postal Code</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['address_info']['postal_code'] ?: 'Not provided' }}</dd>
				</div>
			</dl>
		</div>
	</div>

	<!-- Emergency Contact & Medical History -->
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
		<!-- Emergency Contact -->
		<div class="bg-white rounded-lg shadow p-6">
			<h2 class="text-xl font-semibold mb-4">Emergency Contact</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-sm font-medium text-gray-500">Name</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['emergency_contact']['name'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Phone</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['emergency_contact']['phone'] ?: 'Not provided' }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Relationship</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['emergency_contact']['relationship'] ?: 'Not provided' }}</dd>
				</div>
			</dl>
		</div>

		<!-- Medical History -->
		<div class="bg-white rounded-lg shadow p-6">
			<h2 class="text-xl font-semibold mb-4">Medical History</h2>
			<dl class="space-y-3">
				<div>
					<dt class="text-sm font-medium text-gray-500">Medical consultation in last 2 years</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['medical_history']['consultation_2_years'] }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Serious illness history</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['medical_history']['serious_illness_history'] }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Insurance rejection history</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['medical_history']['insurance_rejection_history'] }}</dd>
				</div>
				<div>
					<dt class="text-sm font-medium text-gray-500">Serious injury history</dt>
					<dd class="text-sm text-gray-900">{{ $medicalInfo['registration_data']['medical_history']['serious_injury_history'] }}</dd>
				</div>
			</dl>
		</div>
	</div>

	<!-- Plan Details -->
	<div class="bg-white rounded-lg shadow p-6 mb-6">
		<h2 class="text-xl font-semibold mb-4">Plan Details</h2>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Plan Type</h3>
				<p class="text-lg font-semibold text-gray-900">{{ $medicalInfo['registration_data']['plan_details']['plan_type'] ?: 'Not specified' }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Payment Mode</h3>
				<p class="text-lg font-semibold text-gray-900">{{ ucfirst($medicalInfo['registration_data']['plan_details']['payment_mode'] ?: 'Not specified') }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Medical Card Type</h3>
				<p class="text-lg font-semibold text-gray-900">{{ $medicalInfo['registration_data']['plan_details']['medical_card_type'] ?: 'Not specified' }}</p>
			</div>
			<div class="bg-gray-50 rounded-lg p-4">
				<h3 class="text-sm font-medium text-gray-500">Premium Amount</h3>
				<p class="text-lg font-semibold text-gray-900">RM {{ number_format($medicalInfo['registration_data']['plan_details']['premium_amount'] ?: 0, 2) }}</p>
			</div>
		</div>
	</div>

@else
	<!-- No Medical Plan -->
	<div class="bg-white rounded-lg shadow p-6 text-center">
		<div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
			<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
			</svg>
		</div>
		<h2 class="text-xl font-semibold text-gray-900 mb-2">No Medical Plan Found</h2>
		<p class="text-gray-500">This user has not registered for any medical insurance plan yet.</p>
	</div>
@endif

@endsection
