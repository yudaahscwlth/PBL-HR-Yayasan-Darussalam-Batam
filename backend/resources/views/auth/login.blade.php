<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistem Informasi Yayasan Darussalam">
    <meta name="keywords" content="Laravel 12, HRM, Admin Template, Bootstrap 5">
    <meta name="author" content="Tim PBL 221">
    <title>Login | YAYASAN DARUSSALAM</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0056b3;
            --primary-hover: #004799;
            --secondary-color: #ffcc00;
            --secondary-hover: #e6b800;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --border-radius: 10px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            overflow-x: hidden;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
            height: 600px;
        }
        
        .login-banner {
            flex: 1;
            background: linear-gradient(135deg, #0056b3 0%, #004080 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .login-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(rgba(255,255,255,0.1), transparent);
            opacity: 0.3;
        }
        
        .login-banner h2 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .banner-logo {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .banner-logo img {
            height: 60px;
            margin-right: 15px;
        }
        
        .banner-logo .vr {
            background-color: rgba(255,255,255,0.3);
            margin: 0 15px;
        }
        
        .banner-logo h4 {
            margin: 0;
            font-weight: 700;
            line-height: 1.3;
            text-transform: uppercase;
        }
        
        .login-form {
            flex: 1;
            background: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form h3 {
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating > label {
            color: #6c757d;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.25);
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: var(--border-radius);
        }
        
        /* Animation for form elements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-animate {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .form-animate:nth-child(1) { animation-delay: 0.1s; }
        .form-animate:nth-child(2) { animation-delay: 0.2s; }
        .form-animate:nth-child(3) { animation-delay: 0.3s; }
        .form-animate:nth-child(4) { animation-delay: 0.4s; }
        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
                max-width: 500px;
            }
            
            .login-banner, .login-form {
                width: 100%;
                padding: 2rem;
            }
            
            .login-banner {
                display: none;
            }
            
            body {
                padding: 1rem;
            }
        }
        .mobile-logo {
            display: none;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 992px) {
            .mobile-logo {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .mobile-logo img {
                height: 40px;
                margin-right: 10px;
            }
            
            .mobile-logo h5 {
                margin: 0;
                font-weight: 700;
                color: var(--primary-color);
                line-height: 1.2;
                text-transform: uppercase;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-banner">
            <div class="banner-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo Darussalam">
                <div class="vr"></div>
                <div>
                    <h4>HR YAYASAN</h4>
                    <h4>DARUSSALAM</h4>
                </div>
            </div>
            <h2>Selamat Datang</h2>
            <p>Sistem Informasi Manajemen SDM Yayasan Darussalam .</p>
        </div>
        
        <div class="login-form">
            <div class="mobile-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo Darussalam">
                <div>
                    <h5>HR YAYASAN</h5>
                    <h5>DARUSSALAM</h5>
                </div>
            </div>
            
            <h3>Login Akun</h3>
            
            <!-- Display validation errors -->
            @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Form login -->
            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf
                
                <!-- Select Role -->
                <div class="form-floating mb-3 form-animate">
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="" selected disabled>Pilih Role</option>
                        <option value="hrd" {{ old('role') == 'hrd' ? 'selected' : '' }}>HRD</option>
                        <option value="kepala_yayasan" {{ old('role') == 'kepala_yayasan' ? 'selected' : '' }}>Kepala Yayasan / Dept</option>
                        <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    </select>
                    <label for="role">Login Sebagai</label>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Username -->
                <div class="form-floating mb-3 form-animate">
                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" placeholder="Username" value="{{ old('username') }}" required>
                    <label for="username">Username</label>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="form-floating mb-3 form-animate">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4 form-animate">
                    <a href="#" class="forgot-password">Lupa Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 form-animate">
                    <i class="feather icon-log-in me-2"></i>Login
                </button>
            </form>
            
            <p class="text-center mt-4 text-muted small">© 2025 PBL 221 - Yayasan Darussalam</p>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/ac-alert.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('login_success') && session('redirect_to'))
        Swal.fire({
            title: 'Login Berhasil',
            text: 'Anda akan diarahkan ke dashboard dalam 3 detik.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            },
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                window.location.href = "{{ session('redirect_to') }}";
            }
        });
        @endif
    });
</script>
