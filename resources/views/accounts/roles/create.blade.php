@extends('layouts.master')

@section('title', __('Create Role'))

@push('css')
<style>
    .heading-design {
        width: 100%;
        text-align: center;
        background-color: grey;
        padding: 5px;
        color: white;
    }
</style>
@endpush

@section('content')
<!-- CONTAINER -->
<div class="main-container container-fluid">

    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">{{ __('Create New Roles') }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">{{ __('Roles') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Create New Roles') }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- Row -->
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-12 col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">
                            {{ __('Create New Roles') }}
                        </h4>

                        <button type="submit" class="btn btn-primary float-right">Save</button>
                    </div>

                    <div class="card-body">
                        <div>
                            <div class="form-group">
                                <div class="d-flex align-items-center">
                                    <label for="name">{{ __('Role Name') }}</label>
                                    <input id="name" type="text" class="m-2 w-50 form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus placeholder="Name">
                                </div>
                                
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label">{{ __('Permissions') }}</label>

                                <div class="row">
                                    <label for="name" class="form-label heading-design">{{ __('Dashboard') }}</label>
                                    @foreach ($permissionsInCategory['Dashboard'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Listing') }}</label>
                                    @foreach ($permissionsInCategory['Listing'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Image Creation') }}</label>
                                    @foreach ($permissionsInCategory['Image'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Inventory') }}</label>
                                    @foreach ($permissionsInCategory['Inventory'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('User') }}</label>
                                    @foreach ($permissionsInCategory['User'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Role & Permissions') }}</label>
                                    @foreach ($permissionsInCategory['Role'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Settings') }}</label>
                                    @foreach ($permissionsInCategory['Settings'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                        
                                    <label for="name" class="form-label heading-design">{{ __('Posts') }}</label>
                                    @foreach ($permissionsInCategory['Post'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <label for="name" class="form-label heading-design">{{ __('Lead/Job Application/Conversion') }}</label>
                                    @foreach ($permissionsInCategory['Jobs'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <label for="name" class="form-label heading-design">{{ __('Marketplace Calculator') }}</label>
                                    @foreach ($permissionsInCategory['Marketplace'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    <label for="name" class="form-label heading-design">{{ __('Marketing and Promotion') }}</label>
                                    @foreach ($permissionsInCategory['Marketing'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('QR Generator') }}</label>
                                    @foreach ($permissionsInCategory['QR'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Office Dispute') }}</label>
                                    @foreach ($permissionsInCategory['Dispute'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                    <label for="name" class="form-label heading-design">{{ __('Task Manager') }}</label>
                                    @foreach ($permissionsInCategory['Manager'] as $permission)
                                    <div class="col-md-4">
                                        <div class="custom-controls-stacked">
                                            <label class="custom-control custom-checkbox-md">
                                                <input type="checkbox" class="custom-control-input" type="checkbox" name="permissions[]" id="permission_{{ $permission['id'] }}" value="{{ $permission['name'] }}">
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}">
                                                    {{ $permission['name'] }}</label>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- End Row -->
</div>
@endsection