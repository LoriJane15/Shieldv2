@extends('layouts.skydash-v')
@section('title', 'User Management')
@section('heading', 'User Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/user-logo-cropper.css') }}">
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-8 col-xl-8 mb-3 mb-xl-0">
            <h3 class="font-weight-bold">User Management</h3>
            <h6 class="mb-0" style="color: rgba(156,156,156,1); font-weight: 300;">
                <span style="color: #280274; font-weight: bold;">Add and manage system users</span> across roles, agencies, and municipalities.
            </h6>
        </div>
        <div class="col-4 col-xl-4">
            <div class="justify-content-end d-flex">
                <a href="#" class="btn btn-primary" style="border-radius: 5px;" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    Add User
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="d-flex flex-wrap justify-content-end align-items-center gap-2 mb-3">
                        <input name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control" style="width:15rem;">
                        <select name="role" class="form-select" style="width:11rem;" onchange="this.form.submit()">
                            <option value="">All roles</option>
                            @foreach ($roles as $key => $meta)
                                <option value="{{ $key }}" @selected(request('role') === $key)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary">Filter</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Logo</th>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Scope</th>
                                    <th>Created at</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $u)
                                    <tr>
                                        <td>
                                            <img src="{{ $u->logo_url }}" alt="{{ $u->name }} logo"
                                                 class="user-table-logo"
                                                 onerror="this.onerror=null;this.src='{{ asset('assets/img/kc-logo.svg') }}'">
                                        </td>
                                        <td class="fw-medium">{{ $u->name }}</td>
                                        <td>{{ '@'.$u->username }}</td>
                                        <td>{{ $roles[$u->role]['label'] ?? $u->role }}</td>
                                        <td>{{ $u->municipality?->name ?? $u->govAgency?->acronym ?? '—' }}</td>
                                        <td>{{ $u->created_at?->timezone(config('app.display_timezone'))->format('M d, Y') ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-end" style="gap:15px;">
                                                <a href="#" class="text-decoration-none" title="Edit"
                                                   data-edit-user
                                                   data-id="{{ $u->id }}"
                                                   data-username="{{ $u->username }}"
                                                   data-name="{{ $u->name }}"
                                                   data-role="{{ $u->role }}"
                                                   data-municipality="{{ $u->municipality_id }}"
                                                   data-agency="{{ $u->gov_agency_id }}"
                                                   data-logo="{{ $u->logo_url }}"
                                                   data-has-logo="{{ $u->logo ? 'true' : 'false' }}"
                                                   data-action="{{ route('super_admin.users.update', $u) }}">
                                                    <i class="icon-pencil edit-icon" style="font-size:18px;"></i>
                                                </a>
                                                @if ($u->id !== auth()->id() && ! $u->rcsp_forms_count && ! $u->implementations_count)
                                                    <button type="button"
                                                            class="border-0 bg-transparent p-0"
                                                            title="Delete user"
                                                            aria-label="Delete {{ $u->name }}"
                                                            data-delete-confirm
                                                            data-delete-action="{{ route('super_admin.users.destroy', $u) }}"
                                                            data-delete-title="Delete user?"
                                                            data-delete-name="{{ $u->name }}"
                                                            data-delete-message="This user account will be permanently removed. This action cannot be undone.">
                                                        <i class="icon-trash delete-icon" style="font-size:18px;"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted" title="{{ $u->id === auth()->id() ? 'You cannot delete your own account' : 'User owns workflow records' }}">
                                                        <i class="icon-lock" style="font-size:18px;"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $users->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    @include('super_admin.partials.delete-confirmation')

    {{-- Add modal --}}
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('super_admin.users.store') }}" enctype="multipart/form-data" data-user-form novalidate>
                        @csrf
                        @include('super_admin.users._fields', ['isEdit' => false])
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                            <button class="btn btn-success">Create user</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit modal (shared, JS-populated) --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" data-user-form data-edit-form novalidate>
                        @csrf @method('PUT')
                        @include('super_admin.users._fields', ['isEdit' => true])
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                            <button class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Role-conditional field visibility (both modals).
        function syncRoleFields(form) {
            const role = form.querySelector('[name=role]').value;
            form.querySelectorAll('[data-role-field]').forEach((el) => {
                el.classList.toggle('d-none', !el.dataset.roleField.split(',').includes(role));
            });
        }

        function setFieldError(field, message) {
            const wrapper = field.closest('.input-group') || field;
            const container = wrapper.parentElement;
            let feedback = container.querySelector('.invalid-feedback[data-client-error]');

            field.classList.add('is-invalid');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block';
                feedback.dataset.clientError = 'true';
                container.appendChild(feedback);
            }
            feedback.textContent = message;
        }

        function clearClientErrors(form) {
            form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
            form.querySelectorAll('[data-client-error]').forEach((el) => el.remove());
        }

        function validateUserForm(form) {
            clearClientErrors(form);

            let firstInvalid = null;
            const requireField = (name, message) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field && !field.value.trim()) {
                    setFieldError(field, message);
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                }
            };

            requireField('name', 'Full name is required.');
            requireField('username', 'Username is required.');
            requireField('role', 'Role is required.');

            const role = form.querySelector('[name=role]').value;
            if (['lgu', 'lswdo', 'dilg_provincial_focal', 'local_eclip_committee'].includes(role)) {
                requireField('municipality_id', 'Municipality is required for municipality-scoped users.');
            }
            if (role === 'gov_agency') {
                requireField('gov_agency_id', 'Government agency is required for agency users.');
            }

            const password = form.querySelector('[name=password]');
            const confirmation = form.querySelector('[name=password_confirmation]');
            const passwordRequired = password.hasAttribute('required');
            const hasPassword = password.value.length > 0;
            const hasConfirmation = confirmation.value.length > 0;

            if (passwordRequired || hasPassword || hasConfirmation) {
                if (!password.value) {
                    setFieldError(password, 'Password is required.');
                    if (!firstInvalid) {
                        firstInvalid = password;
                    }
                } else if (password.value.length < 8) {
                    setFieldError(password, 'Password must be at least 8 characters.');
                    if (!firstInvalid) {
                        firstInvalid = password;
                    }
                }

                if (!confirmation.value) {
                    setFieldError(confirmation, 'Confirm password is required.');
                    if (!firstInvalid) {
                        firstInvalid = confirmation;
                    }
                } else if (password.value !== confirmation.value) {
                    setFieldError(confirmation, 'Passwords do not match.');
                    if (!firstInvalid) {
                        firstInvalid = confirmation;
                    }
                }
            }

            if (firstInvalid) {
                firstInvalid.focus();
                return false;
            }

            return true;
        }

        document.querySelectorAll('[data-user-form]').forEach((form) => {
            const roleSel = form.querySelector('[name=role]');
            roleSel.addEventListener('change', () => syncRoleFields(form));
            form.addEventListener('submit', (event) => {
                if (!validateUserForm(form)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
            form.addEventListener('input', () => clearClientErrors(form));
            syncRoleFields(form);
        });

        document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.input-group').querySelector('input');
                const shouldShow = input.type === 'password';

                input.type = shouldShow ? 'text' : 'password';
                btn.title = shouldShow ? 'Hide password' : 'Show password';
                btn.setAttribute('aria-label', btn.title);
            });
        });

        // Populate + open edit modal.
        document.querySelectorAll('[data-edit-user]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const f = document.querySelector('[data-edit-form]');
                f.action = btn.dataset.action;
                f.querySelector('[name=username]').value = btn.dataset.username;
                f.querySelector('[name=name]').value = btn.dataset.name;
                f.querySelector('[name=role]').value = btn.dataset.role;
                f.querySelector('[name=municipality_id]').value = btn.dataset.municipality || '';
                f.querySelector('[name=gov_agency_id]').value = btn.dataset.agency || '';
                f.querySelector('[name=password]').value = '';
                f.querySelector('[name=password_confirmation]').value = '';
                window.UserLogoCropper?.setExistingLogo(
                    f.querySelector('[data-logo-editor]'),
                    btn.dataset.logo,
                    btn.dataset.hasLogo === 'true'
                );
                syncRoleFields(f);
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
        });

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addUserModal')).show());
        @endif
    </script>
    <script src="{{ asset('assets/js/user-logo-cropper.js') }}"></script>
@endpush
