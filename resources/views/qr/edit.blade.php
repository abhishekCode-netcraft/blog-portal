@extends('layouts.master')

@section('title', 'Edit QR Code')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Edit QR Code</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('qr-resource.update', $qr->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Title</label>

                    <input type="text"
                           class="form-control"
                           value="{{ $qr->title }}"
                           readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">QR URL</label>

                    <input type="text"
                           class="form-control"
                           value="{{ route('qr-resource.redirect', $qr->slug) }}"
                           readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Redirect URL</label>

                    <input type="url"
                           name="redirect_url"
                           class="form-control"
                           value="{{ old('redirect_url', $qr->redirect_url) }}"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update
                </button>

                <a href="{{ route('qr-resource.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>
@endsection