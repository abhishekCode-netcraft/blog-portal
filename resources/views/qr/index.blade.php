@extends('layouts.master')

@section('title', __('QR Code Generator'))

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('QR Codes') }}</h4>

            <a href="{{ route('qr-resource.create') }}" class="btn btn-primary">
                Generate QR
            </a>
        </div>

        <div class="card-body">
            @if($qrs->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Redirect URL</th>
                                <th>QR URL</th>
                                <th width="100">Scans</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($qrs as $qr)
                                <tr>
                                    <td>{{ $qrs->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class='d-flex flex-column align-items-center' style='grid-gap: 5px;'>
                                            <img onerror="this.onerror=null;this.src='/dummy.jpg';" src="{{ asset('storage/'.$qr->qr_image) }}"
                                                width="80"
                                                alt="QR Code">
    
                                            <a href="{{ route('qr-resource.download', $qr->id) }}" class="btn btn-success btn-sm">
                                                Download
                                            </a>
                                        </div>
                                    </td>

                                    <td>{{ $qr->title }}</td>

                                    <td>
                                        <a href="{{ $qr->redirect_url }}"
                                           target="_blank">
                                            {{ Str::limit($qr->redirect_url, 40) }}
                                        </a>
                                    </td>

                                    <td>
                                        <a href="{{ route('qr-resource.redirect', $qr->slug) }}"
                                           target="_blank">
                                            {{ route('qr-resource.redirect', $qr->slug) }}
                                        </a>
                                    </td>

                                    <td>
                                        {{ $qr->scan_count }}
                                    </td>

                                    <td>
                                        <div class='d-flex' style='grid-gap: 5px;'>
                                            {{-- <a href="{{ route('qr-resource.download', $qr->id) }}"
                                            class="btn btn-success btn-sm">
                                                Download
                                            </a> --}}
    
                                            <a href="{{ route('qr-resource.edit', $qr->id) }}"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
    
                                            <form action="{{ route('qr-resource.destroy', $qr->id) }}"
                                                method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
    
                                                <button onclick="return confirm('Are you sure?')" type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $qrs->links() }}
            @else
                <div class="text-center py-4">
                    <p class="mb-0">No QR codes found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).on('submit', '.delete-form', function (e) {
    if (!confirm('Are you sure you want to delete this QR code?')) {
        e.preventDefault();
    }
});
</script>
@endsection