@extends('kepala.master')
@section('title', 'Edit Profil')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
  <li class="breadcrumb-item active">Edit Profil</li>
@endsection

@section('content')
<div class="row">
  <!-- Profil Pengguna -->
  <div class="col-md-12">
    <div class="card mb-4">

      <div class="card-header">
        <h5>Profil Pengguna</h5>
      </div>
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-3 text-center">
            <div class="avatar avatar-xl mb-3">
              @if($pegawai->foto)
                <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" alt="User Profile" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
              @else
                <img src="{{ asset('assets/images/default-avatar.png') }}" alt="Default Profile" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
              @endif
            </div>
            <h4 class="mb-1">{{ $pegawai->nama }}</h4>
            <p class="text-muted">{{ $pegawai->jabatan->nama_jabatan ?? 'Jabatan tidak tersedia' }} - {{ ucfirst(Auth::user()->role) }}</p>
            <button type="button" id="btnEditQuick" class="btn btn-sm text-white" style="background-color: #0056b3;">
              <i class="fas fa-edit"></i> Edit Profile
            </button>
          </div>
          <div class="col-md-9">
            <div class="row">
              <div class="col-md-6">
                <div class="card border mb-3">
                  <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">
                      <i class="fas fa-envelope me-2"></i>Email
                    </h6>
                    <p class="mb-0">{{ $pegawai->email }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border mb-3">
                  <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">
                      <i class="fas fa-phone me-2"></i>No. Telepon
                    </h6>
                    <p class="mb-0">{{ $pegawai->no_hp ?? 'Belum diatur' }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border mb-3">
                  <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">
                      <i class="fas fa-calendar me-2"></i>Bergabung Sejak
                    </h6>
                    <p class="mb-0">{{ $pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('d F Y') : 'Belum diatur' }}</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card border mb-3">
                  <div class="card-body">
                    <h6 class="mb-2 f-w-400 text-muted">
                      <i class="fas fa-building me-2"></i>Departemen
                    </h6>
                    <p class="mb-0">{{ $pegawai->departemen->nama_departemen ?? 'Departemen tidak tersedia' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Edit Profil -->
  <div class="col-md-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-user-edit me-2"></i>Edit Profil
        </h5>
        <button type="button" id="btnEdit" style="background-color: #0056b3;" class="btn text-white btn-sm">
          <i class="fas fa-edit me-1"></i>Edit
        </button>
      </div>
      <div class="card-body">
        <form action="{{ route('kepala.profile.update') }}" method="POST" enctype="multipart/form-data" id="formProfile">
          @csrf
          
          <div class="row">
            <!-- Informasi Personal -->
            <div class="col-md-12 mb-4">
              <h6 class="text-muted border-bottom pb-2 mb-3">
                <i class="fas fa-user me-2"></i>Informasi Personal
              </h6>
            </div>

            <div class="col-md-6 mb-3">
              <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" id="nama" 
                value="{{ old('nama', $pegawai->nama) }}" placeholder="Nama lengkap" required maxlength="255" disabled>
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @endif
            </div>

            <div class="col-md-6 mb-3">
              <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" 
                value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}" placeholder="Tempat lahir" maxlength="100" disabled>
              @error('tempat_lahir')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" 
                value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->format('Y-m-d') : '') }}" disabled>
              @error('tanggal_lahir')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Jenis Kelamin</label>
              <div class="mt-2">
                <div class="form-check form-check-inline">
                  <input type="radio" name="jenis_kelamin" id="pria" class="form-check-input @error('jenis_kelamin') is-invalid @enderror" value="L" 
                    {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'L' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="pria">
                    <i class="fas fa-mars me-1"></i>Laki-laki
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" name="jenis_kelamin" id="wanita" class="form-check-input @error('jenis_kelamin') is-invalid @enderror" value="P" 
                    {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'P' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="wanita">
                    <i class="fas fa-venus me-1"></i>Perempuan
                  </label>
                </div>
              </div>
              @error('jenis_kelamin')
                <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- Informasi Kontak -->
            <div class="col-md-12 mb-4 mt-3">
              <h6 class="text-muted border-bottom pb-2 mb-3">
                <i class="fas fa-address-card me-2"></i>Informasi Kontak
              </h6>
            </div>

            <div class="col-md-12 mb-3">
              <label for="alamat" class="form-label">Alamat Lengkap</label>
              <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" maxlength="255" placeholder="Alamat lengkap" disabled>{{ old('alamat', $pegawai->alamat) }}</textarea>
              @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="no_hp" class="form-label">No. Handphone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" 
                  value="{{ old('no_hp', $pegawai->no_hp) }}" maxlength="20" placeholder="Nomor HP" disabled>
              </div>
              @error('no_hp')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email Aktif <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" 
                  value="{{ old('email', $pegawai->email) }}" maxlength="255" placeholder="Email aktif" required disabled>
              </div>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Foto Profil -->
            <div class="col-md-12 mb-4 mt-3">
              <h6 class="text-muted border-bottom pb-2 mb-3">
                <i class="fas fa-image me-2"></i>Foto Profil
              </h6>
            </div>

            <div class="col-md-12 mb-3">
              <label for="foto" class="form-label">Foto Profil</label>
              <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" id="foto" accept="image/jpg,image/jpeg,image/png" disabled>
              <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>Maksimal 2MB, format: JPG, JPEG, PNG
              </div>
              @if ($pegawai->foto)
                <div class="mt-2">
                  <img src="{{ asset('uploads/pegawai/' . $pegawai->foto) }}" alt="Foto Profil" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                  <small class="d-block text-muted mt-1">Foto saat ini</small>
                </div>
              @endif
              @error('foto')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <button type="button" id="btnCancel" class="btn btn-outline-secondary me-2" disabled>
              <i class="fas fa-times me-1"></i>Batal
            </button>
            <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
              <i class="fas fa-save me-1"></i>Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Form Ganti Password -->
  <div class="col-md-12 mt-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          <i class="fas fa-lock me-2"></i>Keamanan Password
        </h5>
        <button type="button" id="btnEditPassword" style="background-color: #dc3545;" class="btn text-white btn-sm">
          <i class="fas fa-key me-1"></i>Ganti Password
        </button>
      </div>
      <div class="card-body">
        <form action="{{ route('kepala.password.update') }}" method="POST" id="formPassword">
          @csrf
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Perhatian:</strong> Pastikan password baru Anda aman dan mudah diingat. Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.
              </div>
            </div>

            <div class="col-md-12 mb-3">
              <label for="current_password" class="form-label">Password Lama <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" 
                  placeholder="Masukkan password lama" required disabled>
                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword" disabled>
                  <i class="fas fa-eye" id="eyeIconCurrent"></i>
                </button>
              </div>
              @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" 
                  placeholder="Masukkan password baru" required disabled>
                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword" disabled>
                  <i class="fas fa-eye" id="eyeIconNew"></i>
                </button>
              </div>
              <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>Minimal 8 karakter
              </div>
              @error('new_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                <input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation" 
                  placeholder="Ulangi password baru" required disabled>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" disabled>
                  <i class="fas fa-eye" id="eyeIconConfirm"></i>
                </button>
              </div>
              <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>Harus sama dengan password baru
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <button type="button" id="btnCancelPassword" class="btn btn-outline-secondary me-2" disabled>
              <i class="fas fa-times me-1"></i>Batal
            </button>
            <button type="submit" class="btn btn-danger" id="btnSubmitPassword" disabled>
              <i class="fas fa-save me-1"></i>Ganti Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Profile Form Elements
    const btnEdit = document.getElementById('btnEdit');
    const btnEditQuick = document.getElementById('btnEditQuick');
    const btnCancel = document.getElementById('btnCancel');
    const btnSubmit = document.getElementById('btnSubmit');
    const form = document.getElementById('formProfile');

    // Password Form Elements
    const btnEditPassword = document.getElementById('btnEditPassword');
    const btnCancelPassword = document.getElementById('btnCancelPassword');
    const btnSubmitPassword = document.getElementById('btnSubmitPassword');
    const formPassword = document.getElementById('formPassword');
    const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Store original form data
    let originalFormData = new FormData(form);

    // Profile Edit functionality
    [btnEdit, btnEditQuick].forEach(btn => {
      btn.addEventListener('click', function () {
        toggleProfileForm(false);
        document.getElementById('formProfile').scrollIntoView({ behavior: 'smooth' });
      });
    });

    btnCancel.addEventListener('click', function () {
      if (confirm('Apakah Anda yakin ingin membatalkan perubahan?')) {
        toggleProfileForm(true);
        form.reset();
        restoreOriginalValues();
      }
    });

    // Password Edit functionality
    btnEditPassword.addEventListener('click', function () {
      togglePasswordForm(false);
      document.getElementById('formPassword').scrollIntoView({ behavior: 'smooth' });
    });

    btnCancelPassword.addEventListener('click', function () {
      if (confirm('Apakah Anda yakin ingin membatalkan perubahan password?')) {
        togglePasswordForm(true);
        formPassword.reset();
      }
    });

    // Toggle password visibility functions
    function setupPasswordToggle(toggleBtn, passwordInput, eyeIcon) {
      toggleBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
      });
    }

    setupPasswordToggle(toggleCurrentPassword, document.getElementById('current_password'), document.getElementById('eyeIconCurrent'));
    setupPasswordToggle(toggleNewPassword, document.getElementById('new_password'), document.getElementById('eyeIconNew'));
    setupPasswordToggle(toggleConfirmPassword, document.getElementById('new_password_confirmation'), document.getElementById('eyeIconConfirm'));

    function toggleProfileForm(disabled) {
      const elements = form.querySelectorAll('input, select, textarea');
      
      elements.forEach(el => {
        el.disabled = disabled;
        if (!disabled) {
          el.classList.remove('was-validated');
        }
      });

      btnEdit.disabled = !disabled;
      btnEditQuick.disabled = !disabled;
      btnCancel.disabled = disabled;
      btnSubmit.disabled = disabled;

      if (disabled) {
        btnEdit.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        btnEditQuick.innerHTML = '<i class="fas fa-edit me-1"></i>Edit Profile';
      } else {
        btnEdit.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
        btnEditQuick.innerHTML = '<i class="fas fa-times me-1"></i>Cancel Edit';
      }
    }

    function togglePasswordForm(disabled) {
      const elements = formPassword.querySelectorAll('input');
      
      elements.forEach(el => {
        el.disabled = disabled;
        if (!disabled) {
          el.classList.remove('was-validated');
        }
      });

      btnEditPassword.disabled = !disabled;
      btnCancelPassword.disabled = disabled;
      btnSubmitPassword.disabled = disabled;
      toggleCurrentPassword.disabled = disabled;
      toggleNewPassword.disabled = disabled;
      toggleConfirmPassword.disabled = disabled;

      if (disabled) {
        btnEditPassword.innerHTML = '<i class="fas fa-key me-1"></i>Ganti Password';
      } else {
        btnEditPassword.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
      }
    }

    function restoreOriginalValues() {
      const inputs = form.querySelectorAll('input, textarea, select');
      inputs.forEach(input => {
        if (input.type === 'radio') {
          input.checked = input.defaultChecked;
        } else if (input.type !== 'file') {
          input.value = input.defaultValue;
        }
      });
    }

    // Form validation
    form.addEventListener('submit', function(e) {
      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      
      if (!nama || !email) {
        e.preventDefault();
        alert('Nama dan Email wajib diisi!');
        return false;
      }
      
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
      btnSubmit.disabled = true;
    });

    // Password form validation
    formPassword.addEventListener('submit', function(e) {
      const currentPassword = document.getElementById('current_password').value;
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('new_password_confirmation').value;
      
      if (!currentPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Semua field password wajib diisi!');
        return false;
      }

      if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
        return false;
      }

      if (newPassword.length < 8) {
        e.preventDefault();
        alert('Password baru minimal 8 karakter!');
        return false;
      }
      
      btnSubmitPassword.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengubah Password...';
      btnSubmitPassword.disabled = true;
    });
  });
</script>

@if(session('notifikasi'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const alertType = '{{ session("type") }}';
    const alertMessage = '{{ session("notifikasi") }}';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${alertType === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
      <i class="fas fa-${alertType === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
      ${alertMessage}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  });
</script>
@endif
@endpush