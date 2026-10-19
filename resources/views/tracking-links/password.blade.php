@extends('layouts.app')

@section('title', 'Protected Link - Password Required')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 text-center">
                <div class="w-16 h-16 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                    <i class="bi bi-shield-lock-fill fs-1 text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1">Protected Link</h3>
                <p class="text-muted small mb-4">This tracking link is password protected by the author. Enter the password to proceed.</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 text-start">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('tracking.verify-password', $trackingLink->slug) }}" method="POST">
                    @csrf
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label small fw-semibold text-secondary">Access Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password..." required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                        Unlock & Proceed <i class="bi bi-arrow-right-circle ms-1"></i>
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-muted small">
                    Secured by <strong>Laravel 12 Tracking System</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
