@extends('admin.layout.app')

@section('content')
    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-xl-8">
                
                {{-- Success Message --}}
                <div class="text-center my-5">
                    <div class="mb-4">
                        <span class="avatar avatar-xxl bg-success-transparent rounded-circle">
                            <i class="ri-check-line fs-1 text-success"></i>
                        </span>
                    </div>
                    <h3 class="fw-semibold mb-2">🎉 Company Created Successfully!</h3>
                    <p class="text-muted fs-15">
                        Your company <strong>{{ $company->Company_Name }}</strong> has been set up.
                    </p>
                </div>

                {{-- Profile Completion Card --}}
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            Profile Completion
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Progress Bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fs-14 fw-semibold">{{ $percentage }}% Complete</span>
                                <span class="fs-14 text-muted">{{ 100 - $percentage }}% Remaining</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        @if($percentage < 100)
                            {{-- Missing Fields --}}
                            <div class="alert alert-warning mb-0">
                                <h6 class="alert-heading fw-semibold">
                                    <i class="ri-information-line me-1"></i>
                                    Complete your profile to unlock all features:
                                </h6>
                                <ul class="mb-0 ps-3">
                                    @foreach($missingFields as $field)
                                        <li>{{ $field }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="ri-check-line me-1"></i>
                                Your profile is complete! You can now use all features.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Continue Button --}}
                <div class="text-center mb-5">
                    <a href="{{ route('company.dashboard', $company->id) }}" class="btn btn-primary btn-lg px-5">
                        Continue to Dashboard
                        <i class="ri-arrow-right-line ms-2"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection