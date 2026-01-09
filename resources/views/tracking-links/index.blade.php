@extends('layouts.app')

@section('title', 'Tracking Links')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Tracking Links</h1>
    <a href="{{ route('tracking-links.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Link
    </a>
</div>

@if($links->isEmpty())
    <div class="alert alert-info">
        No tracking links found. Create your first one!
    </div>
@else
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Original URL</th>
                    <th>Tracking URL</th>
                    <th>Clicks</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($links as $link)
                <tr>
                    <td>{{ $link->name }}</td>
                    <td>
                        <a href="{{ $link->original_url }}" target="_blank" class="text-truncate" style="max-width: 200px; display: inline-block;">
                            {{ $link->original_url }}
                        </a>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $link->tracking_url }}" readonly id="url-{{ $link->id }}">
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('url-{{ $link->id }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary rounded-pill">{{ $link->clicks_count }}</span>
                    </td>
                    <td>{{ $link->created_at->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('tracking-links.show', $link) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <form action="{{ route('tracking-links.destroy', $link) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

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
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endsection