@extends('layouts.app')

@section('title', 'Tracking Details: ' . $trackingLink->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Link Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8">{{ $trackingLink->name }}</dd>
                    
                    <dt class="col-sm-4">Original URL:</dt>
                    <dd class="col-sm-8">
                        <a href="{{ $trackingLink->original_url }}" target="_blank" class="text-truncate d-block">
                            {{ $trackingLink->original_url }}
                        </a>
                    </dd>
                    
                    <dt class="col-sm-4">Tracking URL:</dt>
                    <dd class="col-sm-8">
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $trackingLink->tracking_url }}" readonly id="tracking-url">
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('tracking-url')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </dd>
                    
                    <dt class="col-sm-4">Total Clicks:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-primary fs-6">{{ $trackingLink->click_count }}</span>
                    </dd>
                    
                    <dt class="col-sm-4">Created:</dt>
                    <dd class="col-sm-8">{{ $trackingLink->created_at->format('F d, Y \a\t h:i A') }}</dd>
                </dl>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Quick Stats</h5>
            </div>
            <div class="card-body">
                @php
                    $clicks = $trackingLink->clicks;
                    $uniqueIps = $clicks->pluck('ip_address')->unique()->count();
                    $browsers = $clicks->groupBy('browser')->map->count();
                    $devices = $clicks->groupBy('device')->map->count();
                @endphp
                
                <div class="mb-3">
                    <strong>Unique IPs:</strong>
                    <span class="badge bg-info">{{ $uniqueIps }}</span>
                </div>
                
                <div class="mb-3">
                    <strong>Top Browsers:</strong>
                    <ul class="list-unstyled">
                        @foreach($browsers->take(3) as $browser => $count)
                        <li>
                            {{ $browser ?: 'Unknown' }}: 
                            <span class="badge bg-secondary">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="mb-3">
                    <strong>Devices:</strong>
                    <ul class="list-unstyled">
                        @foreach($devices as $device => $count)
                        <li>
                            {{ $device ?: 'Unknown' }}: 
                            <span class="badge bg-secondary">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Click History</h5>
            </div>
            <div class="card-body">
                @if($clicks->isEmpty())
                    <div class="alert alert-info">No clicks recorded yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>IP Address</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Browser</th>
                                    <th>Device</th>
                                    <th>Platform</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clicks as $click)
                                <tr>
                                    <td>{{ $click->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $click->ip_address }}</td>
                                    <td>{{ $click->country ?? 'Unknown' }}</td>
                                    <td>{{ $click->city ?? 'Unknown' }}</td>
                                    <td>{{ $click->browser ?? 'Unknown' }}</td>
                                    <td>{{ $click->device ?? 'Unknown' }}</td>
                                    <td>{{ $click->platform ?? 'Unknown' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $clicks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<a href="{{ route('tracking-links.index') }}" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left"></i> Back to Links
</a>

<script>
function copyToClipboard(elementId) {
    const copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    // Show feedback
    const button = copyText.nextElementSibling;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outside-secondary');
    }, 2000);
}
</script>
@endsection