<div class="mb-3">
    <label class="form-label">Acronym</label>
    <input name="acronym" value="{{ old('acronym') }}" required class="form-control" placeholder="DILG">
    @error('acronym') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Name</label>
    <input name="name" value="{{ old('name') }}" required class="form-control" placeholder="Department of the Interior and Local Government">
    @error('name') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
</div>
<div class="mb-3">
    <label class="form-label">Agency logo {{ $isEdit ? '(leave blank to keep current)' : '(optional)' }}</label>
    @if ($isEdit)
        <div class="mb-2" data-agency-logo-current hidden>
            <img data-agency-logo-preview src="{{ asset('assets/img/kc-logo.svg') }}" alt="Current agency logo"
                 style="width:64px;height:64px;border:1px solid #e2e8f0;border-radius:12px;object-fit:contain;padding:4px;">
        </div>
    @endif
    <input name="profile" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
    <p class="mt-1 mb-0 text-muted small">JPG, PNG, or WebP. Maximum 5 MB.</p>
    @error('profile') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
</div>
