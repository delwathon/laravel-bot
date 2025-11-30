@extends('layouts.app')

@section('title', 'Edit User - CryptoBot Pro')

@section('page-title', 'Edit User')

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Users
    </a>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')

    <!-- Personal Information -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-person-circle me-2"></i>Personal Information
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="first_name" class="form-label fw-semibold">
                        First Name
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg @error('first_name') is-invalid @enderror" 
                           id="first_name" name="first_name" 
                           value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label fw-semibold">
                        Last Name
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg @error('last_name') is-invalid @enderror" 
                           id="last_name" name="last_name" 
                           value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">
                        Email Address
                        <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
                           id="email" name="email" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text small">
                        @if($user->email_verified_at)
                            <i class="bi bi-check-circle-fill text-success me-1"></i>Email verified on {{ $user->email_verified_at->format('M d, Y') }}
                        @else
                            <i class="bi bi-exclamation-circle-fill text-warning me-1"></i>Email not verified
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">User ID</label>
                    <input type="text" class="form-control form-control-lg" value="#{{ $user->id }}" disabled>
                    <div class="form-text small">User ID cannot be changed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-shield-lock me-2"></i>Security Settings
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Leave password fields blank to keep the current password
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Leave blank to keep current">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'passwordIcon')">
                            <i class="bi bi-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="form-text small">
                        <i class="bi bi-info-circle me-1"></i>
                        Minimum 8 characters with letters, numbers & symbols
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation', 'passwordConfirmIcon')">
                            <i class="bi bi-eye" id="passwordConfirmIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 p-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-info-circle me-2"></i>Account Information
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Member Since</label>
                        <div class="fw-semibold">{{ $user->created_at->format('M d, Y h:i A') }}</div>
                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Last Updated</label>
                        <div class="fw-semibold">{{ $user->updated_at->format('M d, Y h:i A') }}</div>
                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                        <i class="bi bi-trash me-2"></i>Delete User
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                    <i class="bi bi-info-circle me-2"></i>
                    This action cannot be undone. All user data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const passwordIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.className = 'bi bi-eye-slash';
        } else {
            passwordInput.type = 'password';
            passwordIcon.className = 'bi bi-eye';
        }
    }

    function confirmDelete(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteUserForm').action = '/admin/users/' + userId;
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }
</script>
@endpush