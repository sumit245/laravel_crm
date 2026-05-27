<div class="settings-card">
    <div class="settings-card-header">
        <h4 class="mb-1">Organization Branding</h4>
        <p class="text-muted mb-0">Company identity used across CRM headers, exports, and documents.</p>
    </div>
    <form class="settings-card-body" action="{{ route('settings.organization.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="org_company_name">Company Name <span class="text-danger" aria-hidden="true">*</span></label>
                <input id="org_company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" required aria-required="true" value="{{ old('company_name', $organization->company_name) }}">
                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="org_legal_name">Legal Name</label>
                <input id="org_legal_name" name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ old('legal_name', $organization->legal_name) }}">
                @error('legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="org_gst_number">GST Number</label>
                <input id="org_gst_number" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $organization->gst_number) }}">
                @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="org_phone">Phone</label>
                <input id="org_phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $organization->phone) }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="org_email">Email</label>
                <input id="org_email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $organization->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="org_website">Website</label>
                <input id="org_website" type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $organization->website) }}">
                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="org_address">Address</label>
                <textarea id="org_address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $organization->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="org_logo">Logo</label>
                <input id="org_logo" type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" aria-describedby="org_logo_help">
                <div id="org_logo_help" class="form-text">PNG, JPG, GIF, or WebP. Max 2 MB.</div>
                @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($organization->logo_url)
                    <img src="{{ $organization->logo_url }}" alt="Current organization logo preview" class="mt-2 border rounded" style="max-height: 60px;">
                @endif
            </div>
            <div class="col-md-6">
                <label class="form-label" for="org_favicon">Favicon</label>
                <input id="org_favicon" type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/x-icon,.ico" aria-describedby="org_favicon_help">
                <div id="org_favicon_help" class="form-text">Square image or ICO. Max 1 MB.</div>
                @error('favicon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @if($organization->favicon_url)
                    <img src="{{ $organization->favicon_url }}" alt="Current favicon preview" class="mt-2 border rounded" style="max-height: 40px;">
                @endif
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Organization</button>
        </div>
    </form>
</div>
