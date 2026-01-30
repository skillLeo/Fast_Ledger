{{-- resources/views/auth/register.blade.php --}}

<style>
    .is-invalid+.invalid-feedback {
        display: block;
    }

    .is-invalid {
        border-color: var(--bs-form-invalid-border-color);
    }

    .role-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        height: 100%;
    }

    .role-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }

    .role-card.selected {
        border-color: #667eea;
        background-color: #f7faff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    .role-card input[type="radio"] {
        display: none;
    }

    .role-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .role-title {
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 5px;
    }

    .role-description {
        font-size: 13px;
        color: #718096;
    }
</style>

<x-layout>
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-10 col-sm-11 col-12">
            <!-- Logo Section -->
            <div class="my-5 d-flex justify-content-center">
                <x-logo src="admin/assets/images/brand-logos/desktop-dark.png" alt="logo" href="/" />
            </div>

            <!-- Form Card -->
            <x-form-card title="Sign Up" subtitle="Create your account to get started">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row gy-3">

                        <!-- Role Selection - NEW -->
                        <div class="col-12 mb-3">
                            {{-- <label class="form-label text-default fw-bold">Invoicing App</label> --}}
                            {{-- <div class="row g-3"> --}}
                                
                                <!-- Agent Admin -->
                                {{-- <div class="col-md-4">
                                    <label class="role-card" for="role_agent">
                                        <input type="radio" 
                                               name="account_type" 
                                               id="role_agent" 
                                               value="agent_admin"
                                               {{ old('account_type') == 'agent_admin' ? 'checked' : '' }}
                                               required>
                                        <div class="role-icon">👔</div>
                                        <div class="role-title">Agent Admin</div>
                                        <div class="role-description">
                                            Manage multiple companies and clients
                                        </div>
                                    </label>
                                </div>

                                <!-- Entity Admin -->
                                <div class="col-md-4">
                                    <label class="role-card" for="role_entity">
                                        <input type="radio" 
                                               name="account_type" 
                                               id="role_entity" 
                                               value="entity_admin"
                                               {{ old('account_type') == 'entity_admin' ? 'checked' : '' }}
                                               required>
                                        <div class="role-icon">🏢</div>
                                        <div class="role-title">Entity Admin</div>
                                        <div class="role-description">
                                            Manage your company's accounting
                                        </div>
                                    </label>
                                </div> --}}

                                <!-- Invoicing App -->
                                {{-- <div class="col-md-4">
                                    <label class="role-card" for="role_invoicing"> --}}
                                        <input type="hidden" 
                                               name="account_type" 
                                               id="role_invoicing" 
                                               value="invoicing_app"
                                               {{ old('account_type', 'invoicing_app') == 'invoicing_app' ? 'checked' : '' }}
                                               required>
                                        {{-- <div class="role-icon">📄</div> --}}
                                        {{-- <div class="role-title">Invoicing App</div> --}}
                                        {{-- <div class="role-description">
                                            Create invoices and manage inventory
                                        </div> --}}
                                    {{-- </label>
                                </div> --}}

                            {{-- </div> --}}
                            @error('account_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Full Name Input -->
                        <div class="col-12">
                            <label for="Full_Name" class="form-label text-default">Full Name</label>
                            <input type="text"
                                class="form-control @error('Full_Name') is-invalid @enderror"
                                id="Full_Name" 
                                name="Full_Name" 
                                placeholder="Enter Full Name" 
                                value="{{ old('Full_Name') }}"
                                oninput="hideError('Full_Name')"
                                required
                                autofocus>
                            @error('Full_Name')
                                <div class="invalid-feedback" id="Full_Name-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Username Input -->
                        <div class="col-12">
                            <label for="User_Name" class="form-label text-default">User Name</label>
                            <input type="text"
                                class="form-control @error('User_Name') is-invalid @enderror"
                                id="User_Name" 
                                name="User_Name" 
                                placeholder="Enter Username" 
                                value="{{ old('User_Name') }}"
                                oninput="hideError('User_Name')"
                                required>
                            @error('User_Name')
                                <div class="invalid-feedback" id="User_Name-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Input -->
                        <div class="col-12">
                            <label for="email" class="form-label text-default">Email</label>
                            <input type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email" 
                                name="email" 
                                placeholder="Enter Email" 
                                value="{{ old('email') }}"
                                oninput="hideError('email')"
                                required>
                            @error('email')
                                <div class="invalid-feedback" id="email-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Language Selection -->
                        <div class="col-12">
                            <label for="language" class="form-label text-default">Preferred Language</label>
                            <select class="form-select rounded-0 @error('language') is-invalid @enderror" 
                                    id="language" 
                                    name="language" 
                                    required>
                                <option value="en" {{ old('language', 'en') == 'en' ? 'selected' : '' }}>
                                    🇬🇧 English (UK)
                                </option>
                                <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>
                                    🇪🇸 Español
                                </option>
                            </select>
                            @error('language')
                                <div class="invalid-feedback" id="language-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="col-12">
                            <label for="password" class="form-label text-default">Password</label>
                            <div class="position-relative">
                                <input type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter Password"
                                    oninput="hideError('password')"
                                    required>
                                @error('password')
                                    <div class="invalid-feedback" id="password-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="col-12">
                            <label for="password_confirmation" class="form-label text-default">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    placeholder="Confirm Password"
                                    oninput="hideError('password_confirmation')"
                                    required>
                                @error('password_confirmation')
                                    <div class="invalid-feedback" id="password_confirmation-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Show/Hide Password Toggle -->
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showPasswordToggle">
                                <label class="form-check-label text-muted fw-normal fs-12" for="showPasswordToggle">
                                    Show Passwords
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign Up</button>
                            </div>
                        </div>

                        <!-- Footer Text -->
                        <div class="col-12">
                            <div class="text-center">
                                <p class="text-muted mt-3 mb-0">
                                    Already have an account? 
                                    <a href="{{ route('login') }}" class="text-primary">Sign In</a>
                                </p>
                            </div>
                        </div>

                    </div>
                </form>
            </x-form-card>
        </div>
    </div>
</x-layout>

<script>
    function hideError(field) {
        const input = document.getElementById(field);
        const error = document.getElementById(`${field}-error`);
        if (error) error.style.display = 'none';
        if (input) input.classList.remove('is-invalid');
    }

    // Show/Hide Password Toggle
    document.getElementById('showPasswordToggle').addEventListener('change', function() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        
        if (this.checked) {
            passwordInput.type = 'text';
            confirmPasswordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
            confirmPasswordInput.type = 'password';
        }
    });

    // Role Card Selection Visual Feedback
    document.querySelectorAll('.role-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            // Add selected class to clicked card
            this.classList.add('selected');
        });
    });

    // Set initial selected state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="account_type"]:checked');
        if (checkedRadio) {
            checkedRadio.closest('.role-card').classList.add('selected');
        }
    });
</script>