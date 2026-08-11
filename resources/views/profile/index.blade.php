@extends('layouts.app', ['title' => 'Profil Saya'])

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Profil Saya</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item">Profil Saya</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Kelola Profil</h2>
        <p class="section-lead">Perbarui informasi akun dan kata sandi Anda.</p>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Profil</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required autocomplete="name">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->username) }}" required autocomplete="username">
                                <small class="form-text text-muted">Username dipakai untuk login. Jika diubah, Anda akan keluar dan login ulang.</small>
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required autocomplete="email">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode User</label>
                                        <input type="text" class="form-control" value="{{ $user->kode_user ?? '-' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" class="form-control" value="{{ $user->unit->name ?? '-' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Role</label>
                                <div>
                                    @forelse ($user->getRoleNames() as $role)
                                    <span class="badge badge-primary">{{ $role }}</span>
                                    @empty
                                    <span class="text-muted">-</span>
                                    @endforelse
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="profileSaveBtn">
                                <i class="fas fa-save mr-1"></i> Simpan Profil
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Ganti Password</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}" id="passwordForm">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label>Password Saat Ini <span class="text-danger">*</span></label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                    autocomplete="current-password" required>
                                @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password"
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                    autocomplete="new-password" required>
                                <small class="form-text text-muted">Minimal 8 karakter. Setelah diubah, Anda akan keluar dan login ulang.</small>
                                @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="btn btn-warning" id="passwordSaveBtn">
                                <i class="fas fa-key mr-1"></i> Perbarui Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if (session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if ($errors->any())
            toastr.error('Terdapat kesalahan pada form. Silakan periksa kembali.');
        @endif

        @if ($errors->updatePassword->any())
            toastr.error('Gagal memperbarui password. Silakan periksa kembali.');
        @endif
    });
</script>
@endpush
