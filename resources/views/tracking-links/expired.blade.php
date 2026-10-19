@extends('layouts.app')

@section('title', 'Link Inactive or Expired')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <div class="card shadow-lg border-0 rounded-4 p-5">
            <div class="mb-3 text-warning">
                <i class="bi bi-clock-history display-1"></i>
            </div>
            <h2 class="fw-bold text-danger">This Link Has Expired</h2>
            <p class="text-muted mt-2">
                The tracking link <code>{{ $trackingLink->name }}</code> is no longer active. It may have reached its click limit, validity timeframe, or was deactivated by the creator.
            </p>

            <div class="mt-4">
                <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                    <i class="bi bi-house-door me-1"></i> Back to Homepage
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
