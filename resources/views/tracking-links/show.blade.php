@extends('layouts.app')

@section('title', 'Analytics: ' . $trackingLink->name)

@section('content')
{{-- Breadcrumbs & Header --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('tracking-links.index') }}">Links</a></li>
                <li class="breadcrumb-item active">{{ $trackingLink->name }}</li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold mb-0">
            <i class="bi bi-bar-chart-line text-primary me-2"></i>{{ $trackingLink->name }}
            @if($trackingLink->isPasswordProtected())
                <span class="badge bg-warning text-dark fs-6 ms-2"><i class="bi bi-shield-lock-fill me-1"></i>Protected</span>
            @endif
            @if($trackingLink->isExpired())
                <span class="badge bg-danger fs-6 ms-1"><i class="bi bi-clock-history me-1"></i>Expired</span>
            @else
                <span class="badge bg-success fs-6 ms-1"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
            @endif
        </h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tracking-links.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <a href="{{ route('tracking-links.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Link
        </a>
    </div>
</div>

{{-- Top Metrics Overview Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small fw-semibold text-uppercase">Total Clicks</div>
                    <div class="fs-2 fw-bold mt-1">{{ number_format($totalClicksCount) }}</div>
                </div>
                <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                    <i class="bi bi-cursor-fill fs-4"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">All traffic recorded</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-success text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small fw-semibold text-uppercase">Unique Visitors</div>
                    <div class="fs-2 fw-bold mt-1">{{ number_format($uniqueClicksCount) }}</div>
                </div>
                <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                    <i class="bi bi-person-check-fill fs-4"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">Distinct IP visits</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-info text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small fw-semibold text-uppercase">Real Humans</div>
                    <div class="fs-2 fw-bold mt-1">{{ number_format($humanClicksCount) }}</div>
                </div>
                <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">Filtered from bots</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-secondary text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small fw-semibold text-uppercase">Bots & Crawlers</div>
                    <div class="fs-2 fw-bold mt-1">{{ number_format($botClicksCount) }}</div>
                </div>
                <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                    <i class="bi bi-robot fs-4"></i>
                </div>
            </div>
            <div class="small text-white-50 mt-2">Automated crawlers</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Sidebar: Link Details, QR Studio, Security --}}
    <div class="col-lg-4">
        {{-- Link Info Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-link-45deg text-primary me-1"></i>Link Destination
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Tracking URL (Shareable):</label>
                    <div class="input-group mt-1">
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ $trackingLink->tracking_url }}" readonly id="tracking-url">
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('tracking-url')" title="Copy Link">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted fw-semibold">Original Destination:</label>
                    <p class="mb-0 text-truncate">
                        <a href="{{ $trackingLink->original_url }}" target="_blank" class="text-decoration-none" title="{{ $trackingLink->original_url }}">
                            <i class="bi bi-box-arrow-up-right me-1 small"></i>{{ $trackingLink->original_url }}
                        </a>
                    </p>
                </div>

                @if($trackingLink->utm_source || $trackingLink->utm_campaign)
                <div class="mb-3 p-2.5 bg-light rounded-3 border">
                    <span class="badge bg-secondary mb-1">Active UTM Parameters</span>
                    <ul class="list-unstyled small mb-0 font-monospace text-muted">
                        @if($trackingLink->utm_source) <li>Source: <strong>{{ $trackingLink->utm_source }}</strong></li> @endif
                        @if($trackingLink->utm_medium) <li>Medium: <strong>{{ $trackingLink->utm_medium }}</strong></li> @endif
                        @if($trackingLink->utm_campaign) <li>Campaign: <strong>{{ $trackingLink->utm_campaign }}</strong></li> @endif
                        @if($trackingLink->utm_term) <li>Term: <strong>{{ $trackingLink->utm_term }}</strong></li> @endif
                    </ul>
                </div>
                @endif

                <div class="pt-2 border-top small text-muted">
                    <div class="d-flex justify-content-between py-1">
                        <span>Created:</span>
                        <span class="fw-medium text-dark">{{ $trackingLink->created_at->format('d M, Y h:i A') }}</span>
                    </div>
                    @if($trackingLink->expires_at)
                    <div class="d-flex justify-content-between py-1">
                        <span>Expires On:</span>
                        <span class="fw-medium text-danger">{{ $trackingLink->expires_at->format('d M, Y h:i A') }}</span>
                    </div>
                    @endif
                    @if($trackingLink->click_limit)
                    <div class="d-flex justify-content-between py-1">
                        <span>Click Cap Limit:</span>
                        <span class="fw-medium text-dark">{{ $trackingLink->click_count }} / {{ $trackingLink->click_limit }} clicks</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Custom QR Studio Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-qr-code text-primary me-1"></i>Custom QR Studio
                </h5>
                <span class="badge bg-light text-dark border">SVG / PNG Ready</span>
            </div>
            <div class="card-body text-center p-4">
                <div class="p-3 d-inline-block rounded-3 border shadow-sm" style="background-color: {{ $trackingLink->qr_bg_color ?: '#ffffff' }}">
                    {!! QrCode::size(200)
                        ->color($qrColorRgb[0], $qrColorRgb[1], $qrColorRgb[2])
                        ->backgroundColor($qrBgRgb[0], $qrBgRgb[1], $qrBgRgb[2])
                        ->margin(1)
                        ->generate($trackingLink->tracking_url) !!}
                </div>

                <div class="mt-3 d-flex justify-content-center gap-2">
                    <a href="{{ route('tracking-links.qr.download', $trackingLink) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Download Vector SVG
                    </a>
                </div>
            </div>
        </div>

        {{-- Export Click History Card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-file-earmark-spreadsheet text-success me-1"></i>Export Analytics CSV
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('tracking-links.export.csv', ['trackingLink' => $trackingLink->id, 'filter' => 'all']) }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-calendar4-range me-2"></i> All Recorded Clicks
                    </a>
                    <a href="{{ route('tracking-links.export.csv', ['trackingLink' => $trackingLink->id, 'filter' => 'today']) }}" class="btn btn-outline-success btn-sm text-start">
                        <i class="bi bi-calendar-check me-2"></i> Today's Activity
                    </a>
                    <a href="{{ route('tracking-links.export.csv', ['trackingLink' => $trackingLink->id, 'filter' => '7days']) }}" class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-calendar-week me-2"></i> Last 7 Days
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Section: Interactive Charts and Click History --}}
    <div class="col-lg-8">
        {{-- Click Trends Line Chart --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-graph-up text-primary me-1"></i>Click Trends (Last 7 Days)
                </h5>
                <span class="small text-muted">Daily Volume</span>
            </div>
            <div class="card-body">
                <div style="height: 260px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Doughnut & Bar Charts Grid --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-phone me-1 text-primary"></i>Device Breakdown</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="max-height: 220px; width: 100%;">
                            <canvas id="deviceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-compass me-1 text-primary"></i>Top Browsers</h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="max-height: 220px; width: 100%;">
                            <canvas id="browserChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-globe me-1 text-primary"></i>Top Geographies (Countries)</h6>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 200px;">
                            <canvas id="countryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-arrow-in-up-right me-1 text-primary"></i>Top Referrers / Sources</h6>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 200px;">
                            <canvas id="referrerChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Click History Table with Bot & Unique Badges --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-clock-history text-primary me-1"></i>Detailed Click History
                </h5>
                <span class="badge bg-primary rounded-pill">{{ $clicks->total() }} clicks</span>
            </div>
            <div class="card-body p-0">
                @if($clicks->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-mouse2 fs-2 text-secondary"></i>
                        <p class="mt-2 mb-0">No clicks recorded yet. Share your tracking link to start collecting analytics!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-secondary">
                                <tr>
                                    <th class="ps-3">Timestamp</th>
                                    <th>Visitor / IP</th>
                                    <th>Location</th>
                                    <th>Device / Platform</th>
                                    <th>Browser</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($clicks as $click)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $click->created_at->format('d M, Y') }}</div>
                                        <div class="text-muted small">{{ $click->created_at->format('h:i:s A') }}</div>
                                    </td>
                                    <td>
                                        <code>{{ $click->ip_address }}</code>
                                        @if($click->is_unique)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px;">Unique</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($click->country)
                                            <span class="fw-medium">{{ $click->country }}</span>
                                            <div class="text-muted small">{{ $click->city ?: 'Unknown city' }}</div>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $click->device ?: 'Desktop' }}</div>
                                        <div class="text-muted small">{{ $click->platform ?: 'OS' }}</div>
                                    </td>
                                    <td>
                                        <span>{{ $click->browser ?: 'Unknown' }}</span>
                                    </td>
                                    <td>
                                        @if($click->is_bot)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-robot me-1"></i>Bot</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-person me-1"></i>Human</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $clicks->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Initialization Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);

    // 1. Click Trends Line Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Human Clicks',
                        data: chartData.humanTrend,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#0d6efd',
                    },
                    {
                        label: 'Bot / Crawler Clicks',
                        data: chartData.botTrend,
                        borderColor: '#6c757d',
                        backgroundColor: 'rgba(108, 117, 125, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        borderDash: [5, 5],
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // 2. Devices Doughnut Chart
    const deviceCtx = document.getElementById('deviceChart');
    if (deviceCtx && chartData.deviceLabels.length > 0) {
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.deviceLabels,
                datasets: [{
                    data: chartData.deviceValues,
                    backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 3. Browsers Doughnut Chart
    const browserCtx = document.getElementById('browserChart');
    if (browserCtx && chartData.browserLabels.length > 0) {
        new Chart(browserCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.browserLabels,
                datasets: [{
                    data: chartData.browserValues,
                    backgroundColor: ['#0dcaf0', '#198754', '#d63384', '#6610f2', '#adb5bd']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 4. Countries Horizontal Bar Chart
    const countryCtx = document.getElementById('countryChart');
    if (countryCtx && chartData.countryLabels.length > 0) {
        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: chartData.countryLabels,
                datasets: [{
                    label: 'Visits',
                    data: chartData.countryValues,
                    backgroundColor: '#198754',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // 5. Referrers Horizontal Bar Chart
    const refCtx = document.getElementById('referrerChart');
    if (refCtx && chartData.refLabels.length > 0) {
        new Chart(refCtx, {
            type: 'bar',
            data: {
                labels: chartData.refLabels,
                datasets: [{
                    label: 'Traffic',
                    data: chartData.refValues,
                    backgroundColor: '#fd7e14',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});

function copyToClipboard(elementId) {
    const copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);

    const button = copyText.nextElementSibling;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check2"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-primary');

    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-primary');
    }, 2000);
}
</script>
@endsection