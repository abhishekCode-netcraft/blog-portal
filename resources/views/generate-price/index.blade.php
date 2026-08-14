@extends('layouts.master')

@section('title', __('Generate Price ( API )'))

@section('content')

<div class="card mt-5">
    <div class="card-header">
        <h4>{{ __('Generate Price ( API )') }}</h4>
    </div>

    <div class="card-body">
        <form action='{{ route('generate.price.file') }}' method="POST" enctype="multipart/form-data" id='uploadForm'>
            @csrf

            <div class="row">
                <div class="col-md-4">
                    <input
                        type="file"
                        name="price_file"
                        class="form-control"
                        accept=".xlsx,.xls,.csv"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <button class="btn btn-primary w-100">
                        Upload
                    </button>
                </div>

                <div class="col-md-4">
                    <a href='/sample-download.csv' class="btn btn-primary w-100">
                        Sample Download
                    </a>
                </div>
            </div>
        </form>

        <hr>

        <h5>Uploaded Files</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th width="140">Status</th>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Uploaded On</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($files as $index => $file)
                    <tr>
                        <td>{{ $index + 1 }}</td>

                        <td>
                            @if($file->status == 'Completed')
                                <div class="progress">
                                    <div class="progress-bar bg-success"
                                        style="width:100%">
                                        100%
                                    </div>
                                </div>
                            @elseif($file->status == 'Failed')
                            <div class="progress">
                                <div class="progress-bar bg-danger"
                                    style="width:100%">
                                    Failed
                                </div>
                            </div>
                            @else
                                <div class="progress">
                                    <div id="progress-{{ $file->id }}"
                                        data-status='{{ $file->status }}'
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        style="width:{{ $file->progress }}%">
                                        {{ $file->progress }}%
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td>{{ $file->original_name }}</td>

                        {{-- <td>{{ $file->file_type }}</td> --}}

                        <td>{{ number_format($file->file_size / 1024, 2) }} KB</td>

                        <td>{{ $file->created_at->format('d M Y h:i A') }}</td>

                        <td>
                            <div class='d-flex align-items-center gap-2'>
                                <a href="{{ asset('storage/'.$file->file_path) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-info">
                                    Original File
                                </a>
                                
                                @if($file->status == 'Completed')
                                    <a id="downloadedBtn-{{ $file->id }}"
                                        href='{{ asset('storage/'.$file->processed_file) }}'
                                        class="btn btn-sm btn-success text-white">
                                        <i class='fa fa-download'></i>
                                    </a>
                                @else
                                    <a id="downloadBtn-{{ $file->id }}"
                                        class="btn btn-sm btn-success text-white"
                                        style="display:none">
                                        <i class='fa fa-download'></i>
                                    </a>
                                @endif
                                <form action="{{ route('delete.price.log', $file->id) }}" method="POST" class="ml-2">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger text-white" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class='fa fa-trash'></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            No files uploaded yet.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>
        {{ $files->links() }}
    </div>
</div>

@endsection

@push('js')

<script>
    function checkStatus(id) {
        let timer = setInterval(function () {

            $.get('/api/price-file/' + id + '/status', function (res) {

                $('#progress-'+id)
                    .css('width', res.progress + '%')
                    .text(res.progress + '%');

                if (res.status === 'Completed') {

                    clearInterval(timer);

                    $('#progress-'+id)
                        .removeClass('progress-bar-animated')
                        .addClass('bg-success');

                    $('#downloadBtn-'+id)
                        .show()
                        .attr('href', res.download);

                }

                if (res.status === 'Failed') {

                    clearInterval(timer);

                    $('#progress-'+id)
                        .removeClass('progress-bar-animated')
                        .addClass('bg-danger')
                        .text('Failed');

                }

            });

        }, 2000);
    }

    $(function () {
        $('.progress-bar').each(function () {
            const id = $(this).attr('id');
            const status = $(this).attr('data-status');

            if (id && status == 'Processing' || status == 'Pending') {
                const fileId = id.replace('progress-','');

                console.log(fileId);

                // setTimeout(() => {
                    checkStatus(fileId);
                // }, 10000);
            }
        });
    });
</script>
    
@endpush