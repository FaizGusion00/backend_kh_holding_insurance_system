@php($title = 'Plan Details')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Plan: {{ $plan->name }}</h1>
	<div class="space-x-2">
		<a href="{{ route('admin.plans.edit', $plan) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
		<a href="{{ route('admin.plans.commission-rates', $plan) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Commission Rates</a>
		<a href="{{ route('admin.plans.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Plans</a>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">Plan Information</h3>
		<dl class="space-y-4">
			<div>
				<dt class="text-sm font-medium text-gray-500">Name</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $plan->name }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Slug</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $plan->slug }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Description</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $plan->description ?: 'No description' }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Price</dt>
				<dd class="mt-1 text-sm text-gray-900">
					{{ $plan->price_cents ? 'RM ' . number_format($plan->price_cents / 100, 2) : 'N/A' }}
				</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Commission Type</dt>
				<dd class="mt-1">
					<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
						{{ $plan->uses_percentage_commission ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
						{{ $plan->uses_percentage_commission ? 'Percentage' : 'Fixed Amount' }}
					</span>
				</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Status</dt>
				<dd class="mt-1">
					<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
						{{ $plan->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
						{{ $plan->active ? 'Active' : 'Inactive' }}
					</span>
				</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Created</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $plan->created_at->format('M d, Y H:i') }}</dd>
			</div>
			<div>
				<dt class="text-sm font-medium text-gray-500">Updated</dt>
				<dd class="mt-1 text-sm text-gray-900">{{ $plan->updated_at->format('M d, Y H:i') }}</dd>
			</div>
		</dl>
	</div>

	<div class="bg-white rounded-lg shadow p-6">
		<h3 class="text-lg font-semibold mb-4">Commission Rates</h3>
		@if($plan->commissionRates->count() > 0)
			<div class="space-y-3">
				@foreach($plan->commissionRates->sortBy('level') as $rate)
					<div class="flex justify-between items-center p-3 bg-gray-50 rounded">
						<div>
							<span class="font-medium">Level {{ $rate->level }}</span>
						</div>
						<div class="text-right">
							@if($rate->rate_percent)
								<span class="text-blue-600">{{ $rate->rate_percent }}%</span>
							@elseif($rate->fixed_amount_cents)
								<span class="text-green-600">RM {{ number_format($rate->fixed_amount_cents / 100, 2) }}</span>
							@else
								<span class="text-gray-500">Not set</span>
							@endif
						</div>
					</div>
				@endforeach
			</div>
		@else
			<p class="text-gray-500">No commission rates configured.</p>
		@endif
		<div class="mt-4">
			<a href="{{ route('admin.plans.commission-rates', $plan) }}" 
				class="text-blue-600 hover:text-blue-800 text-sm">Configure Commission Rates →</a>
		</div>
	</div>
</div>
@endsection
