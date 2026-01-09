@extends('layouts.app')

@section('title', 'Create Tracking Link')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Create New Tracking Link</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('tracking-links.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Link Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="original_url" class="form-label">Destination URL</label>
                        <input type="url" class="form-control @error('original_url') is-invalid @enderror" 
                               id="original_url" name="original_url" value="{{ old('original_url') }}" 
                               placeholder="https://example.com" required>
                        @error('original_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter the URL you want to track clicks for.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link"></i> Create Tracking Link
                        </button>
                        <a href="{{ route('tracking-links.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection