@extends('layouts.dashboard')
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Site Settings</h1>
                </div>

                {{-- Success message --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Error messages --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>There were some problems with your input:</strong>
                        </div>
                        <ul class="mb-0 mt-2 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ route('admin.site-settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Enable Home Page --}}
                            <div class="form-check form-switch mb-4 p-3 bg-light rounded">
                                <input class="form-check-input" type="checkbox" name="home_enabled" id="home_enabled"
                                    value="1"
                                    {{ old('home_enabled', $settings->home_enabled ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="home_enabled">
                                    Enable Home Page
                                </label>
                                <small class="text-muted d-block mt-1 ms-4">
                                    Controls whether the public home page is accessible to visitors
                                </small>
                            </div>

                            {{-- Enable Login Page --}}
                            <div class="form-check form-switch mb-4 p-3 bg-light rounded">
                                <input class="form-check-input" type="checkbox" name="login_enabled" id="login_enabled"
                                    value="1"
                                    {{ old('login_enabled', $settings->login_enabled ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="login_enabled">
                                    Enable Login Page
                                </label>
                                <small class="text-muted d-block mt-1 ms-4">
                                    If disabled, normal users cannot access the login page.
                                    Admins can still log in via:
                                    <code class="bg-white px-1 rounded">{{ url('auth/login?admin=1') }}</code>
                                </small>
                            </div>

                            {{-- Enable Register Page --}}
                            <div class="form-check form-switch mb-4 p-3 bg-light rounded">
                                <input class="form-check-input" type="checkbox" name="register_enabled"
                                    id="register_enabled" value="1"
                                    {{ old('register_enabled', $settings->register_enabled ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="register_enabled">
                                    Enable Register Page
                                </label>
                                <small class="text-muted d-block mt-1 ms-4">
                                    Controls whether new users can create accounts on the site
                                </small>
                            </div>

                            {{-- Enable NIN Modification --}}
                            <div class="form-check form-switch mb-4 p-3 bg-light rounded">
                                <input class="form-check-input" type="checkbox" name="nin_mod_enabled" id="nin_mod_enabled"
                                    value="1"
                                    {{ old('nin_mod_enabled', $settings->nin_mod_enabled ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="nin_mod_enabled">
                                    Enable NIN Modification Page
                                </label>
                                <small class="text-muted d-block mt-1 ms-4">
                                    Controls whether users can access nin modification page on the site
                                </small>
                            </div>



                            <div class="mb-4">
                                <label class="form-label fw-medium">Consent & Authorization (NIN Modification)</label>
                                <textarea id="nin_consent" name="nin_consent" class="form-control" rows="10">
        {{ old('nin_consent', $settings->nin_consent ?? '') }}
    </textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium">Consent & Authorization (BVN Modification)</label>
                                <textarea id="bvn_consent" name="bvn_consent" class="form-control" rows="10">
        {{ old('bvn_consent', $settings->bvn_consent ?? '') }}
    </textarea>
                            </div>

                            <!-- Communication Settings -->
                            <div class="mb-4 p-4 bg-light rounded">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="d-flex align-items-center justify-content-center rounded bg-success bg-opacity-10 text-success me-3" style="width: 40px; height: 40px;">
                                        <i class="bi bi-whatsapp fs-5"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Support Channels</h5>
                                </div>

                                <div>
                                    <label for="whatsapp_url" class="form-label fw-medium">WhatsApp Support URL</label>
                                    <div class="input-group border bg-white rounded">
                                        <span class="input-group-text bg-transparent border-0 text-muted">
                                            <i class="bi bi-link-45deg fs-5"></i>
                                        </span>
                                        <input type="url" id="whatsapp_url" name="whatsapp_url"
                                            class="form-control border-0 bg-transparent ps-0"
                                            style="box-shadow: none;"
                                            value="{{ old('whatsapp_url', $settings->whatsapp_url ?? '') }}"
                                            placeholder="https://wa.me/234XXXXXXXXXX">
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        This link will be used across the platform whenever a user initiates a support request via WhatsApp.
                                    </small>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="submit" style="" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>

    <script>
        tinymce.init({
            selector: '#nin_consent',
            height: 450,
            menubar: false,
            plugins: 'lists link table code align',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link | table | code',
            branding: false,
            content_style: "body { font-family: Arial, sans-serif; font-size: 14px; }"
        });

        tinymce.init({
            selector: '#bvn_consent',
            height: 450,
            menubar: false,
            plugins: 'lists link table code align',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link | table | code',
            branding: false,
            content_style: "body { font-family: Arial, sans-serif; font-size: 14px; }"
        });
    </script>
@endpush
