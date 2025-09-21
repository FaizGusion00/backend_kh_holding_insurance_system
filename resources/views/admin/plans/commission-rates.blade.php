@php($title = 'Commission Rates')
@extends('admin.layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
	<h1 class="text-2xl font-semibold">Commission Rates: {{ $plan->name }}</h1>
	<a href="{{ route('admin.plans.show', $plan) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to Plan</a>
</div>

<div class="bg-white rounded-lg shadow p-6">
	<form method="POST" action="{{ route('admin.plans.commission-rates.update', $plan) }}">
		@csrf
		<div class="mb-6">
			<p class="text-sm text-gray-600 mb-4">
				Configure commission rates for each level. 
				{{ $plan->uses_percentage_commission ? 'This plan uses percentage-based commissions.' : 'This plan uses fixed amount commissions.' }}
			</p>
		</div>

		<div class="space-y-4">
			@for($level = 1; $level <= 5; $level++)
				@php($rate = $rates->where('level', $level)->first())
				<div class="border rounded-lg p-4">
					<div class="flex items-center justify-between mb-3">
						<h4 class="font-medium">Level {{ $level }}</h4>
					</div>
					
					<input type="hidden" name="rates[{{ $level - 1 }}][level]" value="{{ $level }}">
					
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						@if($plan->uses_percentage_commission)
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Percentage (%)</label>
								<input type="number" 
									name="rates[{{ $level - 1 }}][rate_percent]" 
									value="{{ old('rates.' . ($level - 1) . '.rate_percent', $rate->rate_percent ?? '') }}"
									step="0.01" min="0" max="100"
									class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Fixed Amount (leave empty)</label>
								<input type="hidden" name="rates[{{ $level - 1 }}][fixed_amount_cents]" value="">
								<input type="text" value="Not applicable for percentage plans" disabled
									class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
							</div>
						@else
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Fixed Amount (cents)</label>
								<input type="number" 
									name="rates[{{ $level - 1 }}][fixed_amount_cents]" 
									value="{{ old('rates.' . ($level - 1) . '.fixed_amount_cents', $rate->fixed_amount_cents ?? '') }}"
									min="0"
									class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
								<p class="text-xs text-gray-500 mt-1">Enter amount in cents (e.g., 1000 = RM10.00)</p>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-2">Percentage (leave empty)</label>
								<input type="hidden" name="rates[{{ $level - 1 }}][rate_percent]" value="">
								<input type="text" value="Not applicable for fixed amount plans" disabled
									class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
							</div>
						@endif
					</div>
				</div>
			@endfor
		</div>

		<div class="mt-6 flex justify-end space-x-3">
			<a href="{{ route('admin.plans.show', $plan) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
			<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Commission Rates</button>
		</div>
	</form>
</div>

@if($rates->count() > 0)
<div class="mt-6 bg-white rounded-lg shadow p-6">
	<h3 class="text-lg font-semibold mb-4">Current Rates Summary</h3>
	<div class="grid grid-cols-1 md:grid-cols-5 gap-4">
		@for($level = 1; $level <= 5; $level++)
			@php($rate = $rates->where('level', $level)->first())
			<div class="text-center p-3 bg-gray-50 rounded">
				<div class="text-sm font-medium text-gray-500">Level {{ $level }}</div>
				<div class="mt-1">
					@if($rate && $rate->rate_percent)
						<span class="text-blue-600 font-semibold">{{ $rate->rate_percent }}%</span>
					@elseif($rate && $rate->fixed_amount_cents)
						<span class="text-green-600 font-semibold">RM {{ number_format($rate->fixed_amount_cents / 100, 2) }}</span>
					@else
						<span class="text-gray-400">Not set</span>
					@endif
				</div>
			</div>
		@endfor
	</div>
</div>
@endif
@endsection
