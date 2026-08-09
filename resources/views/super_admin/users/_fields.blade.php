<div class="row g-3">
    <div class="col-sm-6">
        <label class="form-label">Full name</label>
        <input name="name" value="{{ old('name') }}" required class="form-control">
        @error('name') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label">Username</label>
        <input name="username" value="{{ old('username') }}" required class="form-control">
        @error('username') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label">Password {{ $isEdit ? '(blank = keep)' : '' }}</label>
        <div class="input-group">
            <input name="password" type="password" {{ $isEdit ? '' : 'required' }} class="form-control" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" data-password-toggle title="Show password" aria-label="Show password">
                <i class="ti-eye"></i>
            </button>
        </div>
        @error('password') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>
    <div class="col-sm-6">
        <label class="form-label">Confirm password {{ $isEdit ? '(if changing)' : '' }}</label>
        <div class="input-group">
            <input name="password_confirmation" type="password" {{ $isEdit ? '' : 'required' }} class="form-control" autocomplete="new-password">
            <button type="button" class="btn btn-outline-secondary" data-password-toggle title="Show password" aria-label="Show password">
                <i class="ti-eye"></i>
            </button>
        </div>
    </div>
    <div class="col-sm-6">
        <label class="form-label">Role</label>
        <select name="role" required class="form-select">
            @foreach (config('shield.roles') as $key => $meta)
                <option value="{{ $key }}" @selected(old('role') === $key)>{{ $meta['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6">
        <div class="account-status-control">
            <div>
                <label class="account-status-label" for="{{ $isEdit ? 'edit-is-active' : 'add-is-active' }}">Account status</label>
                <span class="account-status-help">Allow this user to sign in</span>
            </div>
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input account-status-switch" type="checkbox" role="switch" name="is_active" value="1"
                   id="{{ $isEdit ? 'edit-is-active' : 'add-is-active' }}" @checked(old('is_active', true))>
        </div>
        @error('is_active') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>

    {{-- Municipality-scoped roles --}}
    <div data-role-field="lgu,lswdo,dilg_provincial_focal,local_eclip_committee" class="col-sm-6 d-none">
        <label class="form-label">Municipality</label>
        <select name="municipality_id" class="form-select">
            <option value="">Select municipality</option>
            @foreach ($municipalities as $m)
                <option value="{{ $m->id }}">{{ $m->name }}</option>
            @endforeach
        </select>
        @error('municipality_id') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>

    {{-- Gov agency only --}}
    <div data-role-field="gov_agency" class="col-sm-6 d-none">
        <label class="form-label">Government Agency</label>
        <select name="gov_agency_id" class="form-select">
            <option value="">Select agency</option>
            @foreach ($agencies as $a)
                <option value="{{ $a->id }}">{{ $a->acronym }}</option>
            @endforeach
        </select>
        @error('gov_agency_id') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>

    <div class="col-12" data-logo-editor data-is-edit="{{ $isEdit ? 'true' : 'false' }}">
        <label class="form-label d-block">User logo {{ $isEdit ? '' : '(optional)' }}</label>

        <div class="user-logo-summary">
            <div class="user-logo-preview">
                <img data-logo-preview src="{{ asset('assets/img/kc-logo.svg') }}" alt="Logo preview">
            </div>
            <div>
                <p class="mb-1 fw-semibold" data-logo-status>
                    {{ $isEdit ? 'Current logo' : 'No logo selected' }}
                </p>
                <p class="mb-2 text-muted small">JPG, PNG, or WebP. Maximum 5 MB.</p>
                <div class="d-flex flex-wrap gap-2">
                    <label class="btn btn-sm btn-outline-primary mb-0">
                        <span data-logo-choose-label>{{ $isEdit ? 'Change logo' : 'Choose logo' }}</span>
                        <input name="logo" type="file" accept="image/jpeg,image/png,image/webp"
                               class="visually-hidden" data-logo-input>
                    </label>
                    @if ($isEdit)
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-logo-recrop>Crop current logo</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="logo-crop-workspace mt-3" data-logo-crop-workspace hidden>
            <div class="logo-crop-heading">
                <div>
                    <p class="mb-1 fw-semibold">Align and crop logo</p>
                    <p class="mb-0 text-muted small">Drag the image to align it inside the square.</p>
                </div>
                <span class="badge bg-light text-dark">1:1 square</span>
            </div>

            <div class="logo-crop-canvas-wrap">
                <canvas width="512" height="512" data-logo-canvas
                        aria-label="Logo cropping area"></canvas>
                <span class="logo-crop-guide" aria-hidden="true"></span>
            </div>

            <label class="form-label small mt-3" data-logo-zoom-label>
                Zoom
                <input type="range" class="form-range" min="1" max="3" value="1" step="0.01"
                       data-logo-zoom aria-label="Logo zoom">
            </label>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-logo-crop-cancel>
                    Cancel
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-logo-crop-accept>
                    Use cropped logo
                </button>
            </div>
        </div>

        <div class="invalid-feedback d-block" data-logo-error hidden></div>
        @error('logo') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
    </div>
</div>
