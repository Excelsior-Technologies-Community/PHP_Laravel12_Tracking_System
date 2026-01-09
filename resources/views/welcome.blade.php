<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Tracking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="display-4 mb-4">Laravel URL Tracking System</h1>
                <p class="lead mb-4">
                    Track clicks, analyze traffic, and monitor your links with our simple tracking system.
                </p>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="{{ route('tracking-links.index') }}" class="btn btn-primary btn-lg px-4 me-md-2">
                        View All Links
                    </a>
                    <a href="{{ route('tracking-links.create') }}" class="btn btn-outline-primary btn-lg px-4">
                        Create New Link
                    </a>
                </div>
                
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">🔄 URL Shortening</h5>
                                <p class="card-text">Create short, trackable URLs for any destination.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">📊 Click Analytics</h5>
                                <p class="card-text">Track clicks, locations, devices, and browsers.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">🌍 Geolocation</h5>
                                <p class="card-text">See where your clicks are coming from worldwide.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>