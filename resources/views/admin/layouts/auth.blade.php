<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login - KH Holdings</title>
	@vite(['resources/css/app.css','resources/js/app.js'])
	<script>
		// Remember Me functionality
		document.addEventListener('DOMContentLoaded', function() {
			const emailInput = document.querySelector('input[name="email"]');
			const passwordInput = document.querySelector('input[name="password"]');
			const rememberCheckbox = document.querySelector('input[name="remember"]');
			const loginForm = document.querySelector('form[method="POST"]');
			
			// Load saved credentials
			const savedEmail = localStorage.getItem('admin_email');
			const savedPassword = localStorage.getItem('admin_password');
			const rememberMe = localStorage.getItem('admin_remember') === 'true';
			
			let autoFilled = false;
			
			if (savedEmail && rememberMe) {
				emailInput.value = savedEmail;
				emailInput.style.backgroundColor = '#f0f9ff';
				emailInput.style.borderColor = '#3b82f6';
				autoFilled = true;
			}
			if (savedPassword && rememberMe) {
				passwordInput.value = savedPassword;
				passwordInput.style.backgroundColor = '#f0f9ff';
				passwordInput.style.borderColor = '#3b82f6';
				autoFilled = true;
			}
			if (rememberMe) {
				rememberCheckbox.checked = true;
			}
			
			// Show notification if credentials were auto-filled
			if (autoFilled) {
				const notification = document.getElementById('auto-fill-notification');
				if (notification) {
					notification.classList.remove('hidden');
					// Hide notification after 3 seconds
					setTimeout(() => {
						notification.classList.add('hidden');
					}, 3000);
				}
			}
			
			// Handle form submission
			loginForm.addEventListener('submit', function(e) {
				if (rememberCheckbox.checked) {
					// Save credentials
					localStorage.setItem('admin_email', emailInput.value);
					localStorage.setItem('admin_password', passwordInput.value);
					localStorage.setItem('admin_remember', 'true');
				} else {
					// Clear saved credentials
					localStorage.removeItem('admin_email');
					localStorage.removeItem('admin_password');
					localStorage.removeItem('admin_remember');
				}
			});
			
			// Handle remember me checkbox change
			rememberCheckbox.addEventListener('change', function() {
				if (!this.checked) {
					// Clear saved credentials when unchecked
					localStorage.removeItem('admin_email');
					localStorage.removeItem('admin_password');
					localStorage.removeItem('admin_remember');
				}
			});
		});
	</script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
	<div class="min-h-screen flex items-center justify-center">
		<div class="w-full max-w-md">
			@if(session('success'))
				<div class="mb-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
			@endif
			
			@if($errors->any())
				<div class="mb-4 p-3 rounded bg-red-50 text-red-700">
					@foreach($errors->all() as $error)
						<div>{{ $error }}</div>
					@endforeach
				</div>
			@endif
			
			@yield('content')
		</div>
	</div>
</body>
</html>
