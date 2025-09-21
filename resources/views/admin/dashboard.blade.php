@php($title = 'Dashboard')
@extends('admin.layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Total Users</p>
				<p class="text-2xl font-semibold">{{ $stats['total_users'] ?? 0 }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Total Payments</p>
				<p class="text-2xl font-semibold">{{ $stats['total_payments'] ?? 0 }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Total Commissions</p>
				<p class="text-2xl font-semibold">RM {{ number_format($stats['total_commissions'] ?? 0, 2) }}</p>
			</div>
		</div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
		<div class="flex items-center">
			<div class="flex-1">
				<p class="text-sm text-gray-600">Pending Withdrawals</p>
				<p class="text-2xl font-semibold">{{ $stats['pending_withdrawals'] ?? 0 }}</p>
			</div>
		</div>
	</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
	<div class="bg-white p-6 rounded-lg shadow">
		<p class="text-sm text-gray-600">Total Revenue</p>
		<p class="text-2xl font-semibold">RM {{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
	</div>
	<div class="bg-white p-6 rounded-lg shadow">
		<p class="text-sm text-gray-600">Average Payment</p>
		<p class="text-2xl font-semibold">RM {{ number_format($stats['avg_payment_rm'] ?? 0, 2) }}</p>
	</div>
	<div class="bg-white p-6 rounded-lg shadow">
		<p class="text-sm text-gray-600">Active Policies</p>
		<p class="text-2xl font-semibold">{{ $stats['active_policies'] ?? 0 }}</p>
	</div>
	<div class="bg-white p-6 rounded-lg shadow">
		<p class="text-sm text-gray-600">Payment Success Rate</p>
		<p class="text-2xl font-semibold">{{ $stats['success_rate'] ?? 0 }}%</p>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
		<h3 class="text-lg font-semibold mb-4">Payment Trends</h3>
        <div class="relative w-full h-72 md:h-80 lg:h-96 overflow-hidden rounded">
            <canvas id="paymentsChart" class="w-full h-full" style="width: 100%; height: 100%;"></canvas>
        </div>
	</div>
	<div class="bg-white p-6 rounded-lg shadow">
		<h3 class="text-lg font-semibold mb-4">Commission Trends</h3>
        <div class="relative w-full h-72 md:h-80 lg:h-96 overflow-hidden rounded">
            <canvas id="commissionsChart" class="w-full h-full" style="width: 100%; height: 100%;"></canvas>
        </div>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
	<div class="bg-white rounded-lg shadow">
		<div class="p-6 border-b">
			<h3 class="text-lg font-semibold">Recent Payments</h3>
		</div>
		<div class="divide-y">
			@forelse($recentPayments as $payment)
				<div class="p-4 flex justify-between items-center">
					<div>
						<p class="font-medium">{{ $payment->user->name ?? 'Unknown' }}</p>
						<p class="text-sm text-gray-600">RM {{ number_format(($payment->amount_cents ?? 0) / 100, 2) }}</p>
					</div>
					<span class="text-xs px-2 py-1 rounded-full 
						{{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
						{{ ucfirst($payment->status) }}
					</span>
				</div>
			@empty
				<div class="p-4 text-gray-500 text-center">No recent payments</div>
			@endforelse
		</div>
	</div>

	<div class="bg-white rounded-lg shadow">
		<div class="p-6 border-b">
			<h3 class="text-lg font-semibold">Recent Withdrawals</h3>
		</div>
		<div class="divide-y">
			@forelse($recentWithdrawals as $withdrawal)
				<div class="p-4 flex justify-between items-center">
					<div>
						<p class="font-medium">{{ $withdrawal->user->name ?? 'Unknown' }}</p>
						<p class="text-sm text-gray-600">RM {{ number_format(($withdrawal->amount_cents ?? 0) / 100, 2) }}</p>
					</div>
					<span class="text-xs px-2 py-1 rounded-full 
						{{ $withdrawal->status === 'paid' ? 'bg-green-100 text-green-800' : 
						   ($withdrawal->status === 'approved' ? 'bg-blue-100 text-blue-800' :
						   ($withdrawal->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
						{{ ucfirst($withdrawal->status) }}
					</span>
				</div>
			@empty
				<div class="p-4 text-gray-500 text-center">No recent withdrawals</div>
			@endforelse
		</div>
	</div>

	<div class="bg-white rounded-lg shadow">
		<div class="p-6 border-b">
			<h3 class="text-lg font-semibold">Top Agents</h3>
		</div>
		<div class="divide-y">
			@forelse($topAgents as $row)
				<div class="p-4 flex justify-between items-center">
					<p class="font-medium">{{ optional($row->earner)->name ?? 'Unknown' }}</p>
					<p class="text-sm text-gray-700">RM {{ number_format(($row->total_cents ?? 0)/100, 2) }}</p>
				</div>
			@empty
				<div class="p-4 text-gray-500 text-center">No data</div>
			@endforelse
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Payments Chart (Count and Amount)
	const labels = @json($chartLabels ?? []);
	const paymentsCount = @json($paymentsSeries ?? []);
	const paymentsAmount = @json($paymentsAmountSeries ?? []);
	const paymentsAmountCumulative = @json($paymentsAmountCumulative ?? []);
	const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
    new Chart(paymentsCtx, {
		type: 'line',
		data: {
			labels,
			datasets: [
				{
					label: 'Payments (count)',
					data: paymentsCount,
					borderColor: 'rgb(59, 130, 246)',
					backgroundColor: 'rgba(59, 130, 246, 0.15)',
					tension: 0.25,
					yAxisID: 'y',
				},
				{
					label: 'Revenue (RM, cumulative)',
					data: paymentsAmountCumulative,
					borderColor: 'rgb(99, 102, 241)',
					backgroundColor: 'rgba(99, 102, 241, 0.15)',
					tension: 0.25,
					yAxisID: 'y1',
				},
			]
		},
        options: {
			responsive: true,
			maintainAspectRatio: false,
            layout: { padding: { top: 8, right: 12, bottom: 8, left: 12 } },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 14, boxHeight: 14 } },
                tooltip: { mode: 'index', intersect: false }
            },
			scales: {
				y: { beginAtZero: true, title: { display: true, text: 'Count' } },
				y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'RM' } },
			}
		}
	});

	// Commissions Chart (RM)
	const commissionsData = @json($commissionsSeries ?? []);
	const commissionsCumulative = @json($commissionsCumulative ?? []);
	const commissionsCtx = document.getElementById('commissionsChart').getContext('2d');
    new Chart(commissionsCtx, {
		type: 'bar',
		data: {
			labels,
			datasets: [
				{
					label: 'Commissions (RM)',
					data: commissionsData,
					backgroundColor: 'rgba(34, 197, 94, 0.45)',
					borderColor: 'rgb(22, 163, 74)',
					borderWidth: 1
				},
				{
					label: 'Commissions (RM, cumulative)',
					data: commissionsCumulative,
					type: 'line',
					borderColor: 'rgba(16, 185, 129, 1)',
					backgroundColor: 'rgba(16, 185, 129, 0.15)',
					tension: 0.25,
				}
			]
		},
        options: {
			responsive: true,
			maintainAspectRatio: false,
            layout: { padding: { top: 8, right: 12, bottom: 8, left: 12 } },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 14, boxHeight: 14 } },
                tooltip: { mode: 'index', intersect: false }
            },
			scales: {
				y: { beginAtZero: true }
			}
		}
	});
});
</script>
@endsection
