<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PerformHub')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    @include('partials.stylesheets')
    @stack('styles')
</head>
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const passwordInput = document.getElementById(button.dataset.passwordToggle);
                const isPasswordVisible = passwordInput.type === 'text';

                passwordInput.type = isPasswordVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!isPasswordVisible));
                button.setAttribute('aria-label', isPasswordVisible ? 'Show password' : 'Hide password');
                button.title = isPasswordVisible ? 'Show password' : 'Hide password';
                button.querySelector('i').className = isPasswordVisible ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
