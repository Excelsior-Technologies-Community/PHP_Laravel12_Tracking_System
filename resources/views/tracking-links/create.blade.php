@extends('layouts.app')

@section('title', 'Create Tracking Link')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-primary text-white py-3 rounded-top-4">
                <h4 class="mb-0 fs-5"><i class="bi bi-plus-circle me-2"></i>Create New Tracking Link</h4>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('tracking-links.store') }}" method="POST" id="createLinkForm">
                    @csrf
                    
                    {{-- Basic Information --}}
                    <div class="mb-4">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-info-circle me-1"></i> Basic Link Info
                        </h6>
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Link Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Diwali Instagram Promo" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="original_url" class="form-label fw-semibold">Destination URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('original_url') is-invalid @enderror" 
                                   id="original_url" name="original_url" value="{{ old('original_url') }}" 
                                   placeholder="https://example.com/product" required>
                            @error('original_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Where the user should land after tracking.</div>
                        </div>

                        {{-- Custom Vanity Slug --}}
                        <div class="mb-3">
                            <label for="custom_slug" class="form-label fw-semibold d-flex justify-content-between">
                                <span>Custom Vanity Slug (Optional)</span>
                                <span id="slugFeedback" class="small fw-normal"></span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">{{ url('/track') }}/</span>
                                <input type="text" class="form-control @error('custom_slug') is-invalid @enderror" 
                                       id="custom_slug" name="custom_slug" value="{{ old('custom_slug') }}" 
                                       placeholder="my-custom-slug" pattern="[a-zA-Z0-9\-_]+">
                            </div>
                            <div class="form-text">Leave blank to automatically generate a random 8-character slug.</div>
                        </div>
                    </div>

                    {{-- Security & Expiry Controls --}}
                    <div class="mb-4">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-shield-lock me-1"></i> Security & Expiry Controls
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="password" class="form-label fw-semibold">Password Protection</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                    <input type="text" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" value="{{ old('password') }}" 
                                           placeholder="Optional PIN/Pass">
                                </div>
                                <div class="form-text small">Users must enter this password to view.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="expires_at" class="form-label fw-semibold">Expiry Date & Time</label>
                                <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                                <div class="form-text small">Link deactivates after this time.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="click_limit" class="form-label fw-semibold">Click Limit / Cap</label>
                                <input type="number" min="1" class="form-control @error('click_limit') is-invalid @enderror" 
                                       id="click_limit" name="click_limit" value="{{ old('click_limit') }}" placeholder="e.g. 100">
                                <div class="form-text small">Auto-expires after N clicks.</div>
                            </div>
                        </div>
                    </div>

                    {{-- UTM Campaign Builder --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="text-uppercase text-secondary fw-bold small mb-0">
                                <i class="bi bi-funnel me-1"></i> UTM Campaign Builder (Marketing Attribution)
                            </h6>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="collapse" data-bs-target="#utmCollapse">
                                Toggle Options
                            </button>
                        </div>

                        <div class="collapse show" id="utmCollapse">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="utm_source" class="form-label small fw-semibold">UTM Source</label>
                                    <input type="text" class="form-control form-control-sm" id="utm_source" name="utm_source" 
                                           placeholder="e.g. google, instagram, newsletter" value="{{ old('utm_source') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="utm_medium" class="form-label small fw-semibold">UTM Medium</label>
                                    <input type="text" class="form-control form-control-sm" id="utm_medium" name="utm_medium" 
                                           placeholder="e.g. cpc, bio, story, email" value="{{ old('utm_medium') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="utm_campaign" class="form-label small fw-semibold">UTM Campaign</label>
                                    <input type="text" class="form-control form-control-sm" id="utm_campaign" name="utm_campaign" 
                                           placeholder="e.g. festive_sale, product_launch" value="{{ old('utm_campaign') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="utm_term" class="form-label small fw-semibold">UTM Term / Keywords</label>
                                    <input type="text" class="form-control form-control-sm" id="utm_term" name="utm_term" 
                                           placeholder="e.g. web_development, fashion" value="{{ old('utm_term') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Custom QR Code Studio --}}
                    <div class="mb-4">
                        <h6 class="text-uppercase text-secondary fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-qr-code me-1"></i> QR Code Studio Branding
                        </h6>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label for="qr_color" class="form-label fw-semibold">QR Foreground Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="qr_color" name="qr_color" value="{{ old('qr_color', '#000000') }}" title="Choose QR color">
                                    <input type="text" class="form-control" id="qr_color_hex" value="{{ old('qr_color', '#000000') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="qr_bg_color" class="form-label fw-semibold">QR Background Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="qr_bg_color" name="qr_bg_color" value="{{ old('qr_bg_color', '#ffffff') }}" title="Choose QR background">
                                    <input type="text" class="form-control" id="qr_bg_color_hex" value="{{ old('qr_bg_color', '#ffffff') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                        <a href="{{ route('tracking-links.index') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Create Tracking Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync Color Pickers
    const qrColor = document.getElementById('qr_color');
    const qrColorHex = document.getElementById('qr_color_hex');
    const qrBgColor = document.getElementById('qr_bg_color');
    const qrBgColorHex = document.getElementById('qr_bg_color_hex');

    if(qrColor && qrColorHex) {
        qrColor.addEventListener('input', (e) => qrColorHex.value = e.target.value);
    }
    if(qrBgColor && qrBgColorHex) {
        qrBgColor.addEventListener('input', (e) => qrBgColorHex.value = e.target.value);
    }

    // Live AJAX Vanity Slug Validation
    const slugInput = document.getElementById('custom_slug');
    const feedback = document.getElementById('slugFeedback');
    let debounceTimer;

    if (slugInput && feedback) {
        slugInput.addEventListener('input', function() {
            const val = this.value.trim();
            clearTimeout(debounceTimer);
            if (!val) {
                feedback.innerHTML = '';
                return;
            }

            feedback.innerHTML = '<span class="text-muted"><i class="spinner-border spinner-border-sm me-1"></i>Checking...</span>';

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`{{ route('tracking-links.check-slug') }}?slug=${encodeURIComponent(val)}`);
                    const data = await res.json();
                    if (data.available) {
                        feedback.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>${data.message}</span>`;
                        slugInput.classList.remove('is-invalid');
                        slugInput.classList.add('is-valid');
                    } else {
                        feedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>${data.message}</span>`;
                        slugInput.classList.remove('is-valid');
                        slugInput.classList.add('is-invalid');
                    }
                } catch(e) {
                    feedback.innerHTML = '';
                }
            }, 400);
        });
    }
});
</script>
@endsection