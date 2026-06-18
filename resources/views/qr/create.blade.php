@extends('layouts.master')

@section('title', __('QR Code Generator'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>{{ __('QR Code Generator') }}</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('qr-resource.store') }}" method="POST">
                @csrf

                {{-- Title --}}
                <div class="mb-3">
                    <label for="title" class="form-label">
                        {{ __('Title') }}
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Enter title"
                        required
                    >
                </div>

                {{-- Redirect URL --}}
                <div class="mb-3">
                    <label for="redirect_url" class="form-label">
                        {{ __('Redirect URL') }}
                    </label>
                    <input
                        type="url"
                        class="form-control"
                        id="redirect_url"
                        name="redirect_url"
                        value="{{ old('redirect_url') }}"
                        placeholder="https://example.com"
                        required
                    >
                </div>

                {{-- URL Type --}}
                <div class="mb-3">
                    <label class="form-label d-block">
                        {{ __('URL Type') }}
                    </label>

                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="url_type"
                            id="auto_url"
                            value="auto"
                            {{ old('url_type', 'auto') == 'auto' ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="auto_url">
                            {{ __('Auto URL') }}
                        </label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="url_type"
                            id="custom_url"
                            value="custom"
                            {{ old('url_type') == 'custom' ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="custom_url">
                            {{ __('Custom URL') }}
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="customSlugDiv" style="display:none;">
                    <label class="form-label">Custom URL Slug</label>

                    <input
                        type="text"
                        name="custom_slug"
                        class="form-control"
                        placeholder="my-offer"
                        value="{{ old('custom_slug') }}"
                    >

                    <small>
                        <b>QR URL</b>:
                        {{ url('/q') }}/your-slug
                    </small>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary">
                    {{ __('Generate QR Code') }}
                </button>
            </form>

            @if(isset($qrCode))
                <hr>

                <div class="text-center mt-4">
                    <h5>Generated QR Code</h5>

                    {!! $qrCode !!}

                    <div class="mt-3">
                        <a href="{{ route('qr-resource.single.download') }}"
                           class="btn btn-success">
                            Download QR Code
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('js')
    
<script>
    $(document).ready(function () {
        function toggleCustomSlug() {
            let urlType = $('input[name="url_type"]:checked').val();

            if (urlType === 'custom') {
                $('#customSlugDiv').slideDown();
            } else {
                $('#customSlugDiv').slideUp();
                $('input[name="custom_slug"]').val('');
            }
        }

        // Initial load
        toggleCustomSlug();

        // On radio change
        $('input[name="url_type"]').on('change', function () {
            toggleCustomSlug();
        });

    });
</script>
@endpush