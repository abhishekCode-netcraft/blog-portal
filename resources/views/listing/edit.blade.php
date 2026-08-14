@extends('layouts.master')

@section('title', __('Update Listing'))

@section('content')
<!-- CONTAINER -->
<div class="main-container container-fluid">

    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">{{ __('Update Listing') }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">{{ __('Listing') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Update Listing') }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- Row -->

    <div class="row">
        <div class="col-md-9 col-xl-12 fields">
            <form action="{{ route('listing.update', $post->id??$productId) }}" method="POST" enctype='multipart/form-data' id='form'>
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">
                            {{ __('Update Listing') }}
                        </h4>

                        @can('Create Duplicate Listing Button')
                        <button type='submit' class="btn btn-info duplicate_listing">Create Duplicate Listing</button>
                        <!-- <a href="{{ route('copy_database', $post->id) }}" class="btn btn-info duplicate_listing">{{ __('Create Duplicate Listing') }} </a> -->
                        @endcan
                        <!-- <button type="submit" class="btn btn-primary float-right">Save</button> -->
                    </div>

                    <div class="card-body">
                        @include('components.fetch-product')
                        <div id="progressBar" class="text-end"></div>

                        <div>
                            <div class="form-group">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="title" class="form-label">{{ __('Product Title') }}<span class="text-danger">*</span> <span class="text-success">(Product Name | Author | Edition | Publication ( Medium ) )</span></label>
                                    <span id="charCount">0/160</span>
                                </div>
                                <label for="description" class="form-label d-flex justify-content-between text-danger" style="margin-top: -10px;">
                                    <div>{{ __('Excess Capitalism in Product Title Not Allowed') }}</div>
                                </label>

                                <input minlength="75" maxlength="160" id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') ?? $post->title??$productTitle }}" autocomplete="title" autofocus placeholder="title">
                                <span class="error-message title" style="color:red;"></span>

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label d-flex justify-content-between">
                                    <div>{{ __('Product Description') }}<span class="text-danger">*</span><span class="text-success"> (Suggestion - Title + Description + Search Key) </span></div>
                                    <div class="d-flex">
                                        <a href='{{ $siteSetting->listing_button_1_link }}' target='_blank'>{{ $siteSetting->listing_button_1 }} | &nbsp;</a><a target='_blank' href="{{ $siteSetting->listing_button_2_link }}"> {{ $siteSetting->listing_button_2 }} | </a>
                                        <a href='https://www.commontools.org/tool/replace-new-lines-with-commas-40' target='_blank'>&nbsp;Line Remover</a>
                                    </div>
                                </label>
                                <label for="description" class="form-label d-flex justify-content-between text-danger" style="margin-top: -10px;">
                                    <div>{{ __('Do not use 3rd Party Links/Website Names') }}</div>
                                </label>
                                <!-- <label for="description" class="form-label d-flex justify-content-between">
                                    <div>{{ __('Product Description') }}<span class="text-danger">*</span><span class="text-danger"> ( Enter Detail Description without using 3rd party link) </span></div><a target='_blank' href="https://chat.openai.com">ChatGPT</a>
                                </label> -->
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" placeholder="Description" rows="10" id='desc'>{!! old('description') ?? $allInfo['desc'] !!}</textarea>
                                <span class="error-message desc" style="color:red;"></span>

                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="mrp" class="form-label">{{ __('MRP') }}<span class="text-danger">*</span><span class="text-success"> ( Maximum Retail Price)</span></label>
                                <input id="mrp" type="number" class="form-control @error('mrp') is-invalid @enderror" name="mrp" value="{{ old('mrp') ?? $allInfo['mrp']}}" autocomplete="mrp" autofocus placeholder="MRP">

                                @error('mrp')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label for="discount" class="form-label">{{ __('Discount ( % )') }}</label>
                                <input id="discount" name="discount" type="number" class="form-control" placeholder="Discount ( % )">
                                <span class="error-message discount" style="color:red;"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="selling_price" class="form-label d-flex justify-content-between">
                                    <div>{{ __('Selling Price') }}<span class="text-danger">*</span></div>
                                    <div>
                                        <a href='{{ $siteSetting->calc_link }}' target='_blank'>Calculator</a>
                                        <!--<a target='_blank' href="https://docs.google.com/spreadsheets/d/1uSqo6RhsLHaVcVrkEjO_SmOWiXqWBC-aV1LvsowgsL0/"> Disc. Info.</a>-->

                                    </div>
                                </label>
                                <input id="selling_price" type="number" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" value="{{ old('selling_price') ?? $allInfo['selling']  }}" autocomplete="selling_price" autofocus placeholder="Selling Price">
                                @include('generate-price.single-price')

                                @error('selling_price')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!--<div class="card">-->
                <!--    <div class="card-body" style="background-color: antiquewhite;">-->
                <!--        <h4>PRICE CALCULATION</h4>-->
                <!--        <div class="row">-->
                <!--            <div class="form-group col-md-4">-->
                <!--                <label for="pub_name" class="form-label">{{ __('Publication') }}</label>-->
                <!--                <select class="genre form-control" name="pub_name" id="pub_name">-->
                <!--                    <option value="">--Publication--</option>-->
                <!--                    @foreach($publications as $pub)-->
                <!--                    <option value="{{ $pub->id }}">{{ $pub->pub_name }}</option>-->
                <!--                    @endforeach-->
                <!--                </select>-->


                <!--            </div>-->

                <!--            <div class="form-group col-md-4">-->
                <!--                <label for="book_name" class="form-label">{{ __('Book Type') }}</label>-->
                <!--                <select class="form-control" name="book_name" id="book_name">-->
                <!--                    <option value="">-- Select Book --</option>-->
                <!--                </select>-->
                <!--            </div>-->


                <!--            <div class="form-group col-md-4">-->
                <!--                <label class="form-label">{{ __('Selling Prices') }}</label>-->
                <!--                <div class="selling-prices">-->
                <!--                    <strong>Min Selling Price:</strong> <span id="selling_price1">--</span> <br>-->
                <!--                    <strong>Max Selling Price:</strong> <span id="selling_price2">--</span>-->
                <!--                </div>-->
                <!--            </div>-->


                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->

                @include('price-calculator')

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="publication" class="form-label">{{ __('Publisher') }}<span class="text-danger">*</span></label>
                                    <span class="charCount">0/35</span>
                                </div>
                                <input maxlength="35" id="publication" type="text" class="form-control @error('publication') is-invalid @enderror" name="publication" value="{{ old('publication') ?? $allInfo['publication'] }}" autocomplete="publication" autofocus placeholder="Publication">
                                <span class="error-message publication" style="color:red;"></span>

                                @error('publication')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="author_name" class="form-label">{{ __('Author Name') }}<span class="text-danger">*</span></label>
                                    <span class="charCount">0/35</span>
                                </div>

                                <input maxlength="35" id="author_name" type="text" class="form-control @error('author_name') is-invalid @enderror" name="author_name" value="{{ old('author_name') ?? $allInfo['author_name'] }}" autocomplete="author_name" autofocus placeholder="Author name">
                                <span class="error-message author_name" style="color:red;"></span>

                                @error('author_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="edition" class="form-label">{{ __('Edition') }}</label>
                                    <span class="charCount">0/20</span>
                                </div>

                                <input maxlength="20" id="edition" type="text" class="form-control @error('edition') is-invalid @enderror" name="edition" value="{{ old('edition') ?? $allInfo['edition']  }}" autocomplete="edition" autofocus placeholder="Edition">
                                <span class="error-message edition" style="color:red;"></span>

                                @error('edition')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-between-center">
                                <label for="label" class="form-label">{{ __('Category') }}<span class="text-danger">*</span><span class="text-danger"> ( Publication, 1 Category, 1 Tag, Others ) </span></label>

                                <div class="d-flex flex-column">
                                    <div id='count'><strong>Label Selected</strong> : 1</div>
                                    <div id='textLength'></div>
                                </div>
                            </div>
                            <label for="description" class="form-label d-flex justify-content-between text-danger" style="margin-top: -10px;">
                                <div>{{ __('Note - You need to Select Min. 8 Perfect Categories to go Your Listing Live. Else It may be Rejected.') }}</div>
                            </label>
                            <select class="form-control select2  @error('label') is-invalid @enderror" data-placeholder="Choose Label" multiple value="{{ old('label') }}" name="label[]">
                                @foreach($categories as $category)
                                <option value="{{ $category['term'] }}" @foreach($labels as $label) @if($category['term']==$label) selected @endif @endforeach>
                                    {{$category['term']}}
                                </option>
                                @endforeach
                            </select>

                            @error('label')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="row" id="addUrls">
                                    <div class="col-md-4">
                                        <div class="">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label for="sku" class="form-label">
                                                    {{ __('ISBN 10') }}
                                                </label>
                                            </div>
                                            <input type="text" class="form-control " name="isbn_10" value="{{ $allInfo['isbn_10'] }}" autofocus placeholder="ISBN 10">
                                            <span class="error-message isbn_10" style="color:red;"></span>

                                            @error('isbn_10')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label for="sku" class="form-label">
                                                    {{ __('ISBN 13') }}
                                                </label>
                                            </div>
                                            <input id="isbn_13" type="text" class="form-control @error('isbn_13') is-invalid @enderror" name="isbn_13" value="{{ $allInfo['isbn_13'] }}" autofocus placeholder="ISBN 13">
                                            <span class="error-message isbn_13" style="color:red;"></span>

                                            @error('isbn_13')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label for="publish_year" class="form-label">
                                                    {{ __('Publish Year') }}
                                                </label>
                                            </div>
                                            @php
                                            $currentMonthYear = date('Y-m'); // Generate current year and month in "YYYY-MM" format
                                            @endphp

                                            <input
                                                type="month"
                                                class="form-control @error('publish_year') is-invalid @enderror"
                                                name="publish_year"
                                                value="{{ old('publish_year', $allInfo['publish_year'] ?? $currentMonthYear) }}">
                                            <span class="error-message publish_year" style="color:red;"></span>

                                            @error('publish_year')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- SKU -->
                                    <div class="col-md-4">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label for="sku" class="form-label">
                                                    {{ __('SKU') }}
                                                    <span class="text-danger">*</span>
                                                    <span class="text-danger">( Short Code )</span>
                                                </label>
                                                <span class="charCount">0/30</span>
                                            </div>
                                            <input maxlength="30" id="sku" type="text" class="form-control @error('sku') is-invalid @enderror" name="sku" value="{{ old('sku') ?? $allInfo['sku'] }}" autocomplete="sku" autofocus placeholder="SKU">
                                            <span class="error-message sku" style="color:red;"></span>
                                            @error('sku')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Language -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="language" class="form-label">{{ __('Language') }}<span class="text-danger">*</span></label>
                                            <input id="language" type="text" class="form-control @error('language') is-invalid @enderror" name="language" value="{{ old('language') ?? $allInfo['lang'] }}" autocomplete="language" autofocus placeholder="Language">
                                            <span class="error-message language" style="color:red;"></span>
                                            @error('language')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Pages -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="pages" class="form-label">{{ __('No. of Pages') }}</label>
                                            <input id="pages" type="text" class="form-control @error('pages') is-invalid @enderror" name="pages" value="{{ old('pages') ?? $allInfo['page_no'] }}" autocomplete="pages" autofocus placeholder="No. of Pages">
                                            <span class="error-message pages" style="color:red;"></span>
                                            @error('pages')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Condition -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="condition" class="form-label">{{ __('Product Condition') }}<span class="text-danger">*</span></label>
                                            <select class="form-control @error('condition') is-invalid @enderror" name="condition">
                                                <option value="">--Select--</option>
                                                <option value="New" {{ $allInfo['condition'] == 'New' ? 'selected' : '' }}>New</option>
                                                <option value="Like New" {{ $allInfo['condition'] == 'Like New' ? 'selected' : '' }}>Like New</option>
                                                <option value="Old" {{ $allInfo['condition'] == 'Old' ? 'selected' : '' }}>Old</option>
                                            </select>
                                            <span class="error-message condition" style="color:red;"></span>
                                            @error('condition')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Binding Type -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="binding" class="form-label">{{ __('Binding Type') }}<span class="text-danger">*</span></label>
                                            <select class="form-control @error('binding') is-invalid @enderror" name="binding">
                                                <option value="">--Select--</option>
                                                <option value="Hardcover" {{ $allInfo['binding'] == 'Hardcover' ? 'selected' : '' }}>Hardcover</option>
                                                <option value="Paperback" {{ $allInfo['binding'] == 'Paperback' ? 'selected' : '' }}>Paperback</option>
                                            </select>
                                            <span class="error-message binding" style="color:red;"></span>
                                            @error('binding')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="weight" class="form-label d-flex justify-content-between">{{ __('Weight (grams)') }}</label>
                                            <input type="number" class="form-control @error('weight') is-invalid @enderror" name="weight" value="{{ $allInfo['weight'] }}" autocomplete="weight" autofocus placeholder="Weight (g)">
                                            <span class="error-message weight" style="color:red;"></span>

                                            @error('weight')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="reading_age" class="form-label d-flex justify-content-between">{{ __('Reading Age') }}</label>
                                            <select class="form-control @error('reading_age') is-invalid @enderror" name="reading_age">
                                                <option value="">--Select--</option>
                                                <option value="Above 18 Years" {{ $allInfo['reading_age'] == 'Above 18 Years' ? 'selected' : '' }}>Above 18 Years</option>
                                                <option value="Above 10 Years" {{ $allInfo['reading_age'] == 'Above 10 Years' ? 'selected' : '' }}>Above 10 Years</option>
                                                <option value="Above 5 Years" {{ $allInfo['reading_age'] == 'Above 5 Years' ? 'selected' : '' }}>Above 5 Years</option>
                                                <option value="Above 3 Years" {{ $allInfo['reading_age'] == 'Above 3 Years' ? 'selected' : '' }}>Above 3 Years</option>
                                            </select>
                                            <!-- <input style="background-color: #e9f85c;" id="reading_age" type="text" class="form-control @error('reading_age') is-invalid @enderror" name="reading_age" value="{{ $allInfo['reading_age']??'Above 10 Years' }}" autocomplete="reading_age" autofocus placeholder="Reading Age"> -->
                                            <span class="error-message reading_age" style="color:red;"></span>

                                            @error('reading_age')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="country_origin" class="form-label d-flex justify-content-between">{{ __('Country of Origin') }}</label>
                                            <input style="background-color: #e9f85c;" id="country_origin" type="country_origin" class="form-control @error('country_origin') is-invalid @enderror" name="country_origin" readonly value="@if($allInfo['country_origin']) {{ $allInfo['country_origin'] }} @else India @endif" autocomplete="country_origin" autofocus placeholder="Country of Origin">
                                            <span class="error-message country_origin" style="color:red;"></span>

                                            @error('country_origin')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="genre" class="form-label d-flex justify-content-between">{{ __('Genre') }}</label>
                                            <select class="searchable_dropdown form-control @error('genre') is-invalid @enderror" name="genre">
                                                <option value="">--Select--</option>
                                                <option value="Fantasy" {{ $allInfo['genre'] == 'Fantasy' ? 'selected' : '' }}>Fantasy</option>
                                                <option value="Horror" {{ $allInfo['genre'] == 'Horror' ? 'selected' : '' }}>Horror</option>
                                                <option value="Romance" {{ $allInfo['genre'] == 'Romance' ? 'selected' : '' }}>Romance</option>
                                                <option value="Science fiction" {{ $allInfo['genre'] == 'Science fiction' ? 'selected' : '' }}>Science fiction</option>
                                                <option value="Adventure fiction" {{ $allInfo['genre'] == 'Adventure fiction' ? 'selected' : '' }}>Adventure fiction</option>
                                                <option value="Fiction" {{ $allInfo['genre'] == 'Fiction' ? 'selected' : '' }}>Fiction</option>
                                                <option value="Mystery" {{ $allInfo['genre'] == 'Mystery' ? 'selected' : '' }}>Mystery</option>
                                                <option value="Fairy tale" {{ $allInfo['genre'] == 'Fairy tale' ? 'selected' : '' }}>Fairy tale</option>
                                                <option value="Thriller" {{ $allInfo['genre'] == 'Thriller' ? 'selected' : '' }}>Thriller</option>
                                                <option value="Young adult" {{ $allInfo['genre'] == 'Young adult' ? 'selected' : '' }}>Young adult</option>
                                                <option value="Historical" {{ $allInfo['genre'] == 'Historical' ? 'selected' : '' }}>Historical</option>
                                                <option value="Literary fiction" {{ $allInfo['genre'] == 'Literary fiction' ? 'selected' : '' }}>Literary fiction</option>
                                                <option value="Comedy" {{ $allInfo['genre'] == 'Comedy' ? 'selected' : '' }}>Comedy</option>
                                                <option value="Crime" {{ $allInfo['genre'] == 'Crime' ? 'selected' : '' }}>Crime</option>
                                                <option value="Short story" {{ $allInfo['genre'] == 'Short story' ? 'selected' : '' }}>Short story</option>
                                                <option value="Classics" {{ $allInfo['genre'] == 'Classics' ? 'selected' : '' }}>Classics</option>
                                                <option value="Drama" {{ $allInfo['genre'] == 'Drama' ? 'selected' : '' }}>Drama</option>
                                                <option value="Dystopian Fiction" {{ $allInfo['genre'] == 'Dystopian Fiction' ? 'selected' : '' }}>Dystopian Fiction</option>
                                                <option value="Gothic fiction" {{ $allInfo['genre'] == 'Gothic fiction' ? 'selected' : '' }}>Gothic fiction</option>
                                                <option value="Graphic novel" {{ $allInfo['genre'] == 'Graphic novel' ? 'selected' : '' }}>Graphic novel</option>
                                                <option value="Magic realism" {{ $allInfo['genre'] == 'Magic realism' ? 'selected' : '' }}>Magic realism</option>
                                                <option value="Mystery and suspense" {{ $allInfo['genre'] == 'Mystery and suspense' ? 'selected' : '' }}>Mystery and suspense</option>
                                                <option value="Paranormal romance" {{ $allInfo['genre'] == 'Paranormal romance' ? 'selected' : '' }}>Paranormal romance</option>
                                                <option value="School Books" {{ $allInfo['genre'] == 'School Books' ? 'selected' : '' }}>School Books</option>
                                                <option value="NCERT Books" {{ $allInfo['genre'] == 'NCERT Books' ? 'selected' : '' }}>NCERT Books</option>
                                                <option value="Competitive Books" {{ $allInfo['genre'] == 'Competitive Books' ? 'selected' : '' }}>Competitive Books</option>
                                                <option value="Medical Books" {{ $allInfo['genre'] == 'Medical Books' ? 'selected' : '' }}>Medical Books</option>
                                                <option value="Dental Books" {{ $allInfo['genre'] == 'Dental Books' ? 'selected' : '' }}>Dental Books</option>
                                                <option value="Action & Adventure" {{ $allInfo['genre'] == 'Action & Adventure' ? 'selected' : '' }}>Action & Adventure</option>
                                                <option value="Arts, Film & Photography" {{ $allInfo['genre'] == 'Arts, Film & Photography' ? 'selected' : '' }}>Arts, Film & Photography</option>
                                                <option value="Biographies, Diaries & True Accounts" {{ $allInfo['genre'] == 'Biographies, Diaries & True Accounts' ? 'selected' : '' }}>Biographies, Diaries & True Accounts</option>
                                                <option value="Business & Economics" {{ $allInfo['genre'] == 'Business & Economics' ? 'selected' : '' }}>Business & Economics</option>
                                                <option value="Children's Books" {{ $allInfo['genre'] == "Children's Books" ? 'selected' : '' }}>Children's Books</option>
                                                <option value="Comics & Graphic Novels" {{ $allInfo['genre'] == 'Comics & Graphic Novels' ? 'selected' : '' }}>Comics & Graphic Novels</option>
                                                <option value="Computers & Internet" {{ $allInfo['genre'] == 'Computers & Internet' ? 'selected' : '' }}>Computers & Internet</option>
                                                <option value="Crafts, Hobbies & Home" {{ $allInfo['genre'] == 'Crafts, Hobbies & Home' ? 'selected' : '' }}>Crafts, Hobbies & Home</option>
                                                <option value="Crime, Thriller & Mystery" {{ $allInfo['genre'] == 'Crime, Thriller & Mystery' ? 'selected' : '' }}>Crime, Thriller & Mystery</option>
                                                <option value="Engineering Books" {{ $allInfo['genre'] == 'Engineering Books' ? 'selected' : '' }}>Engineering Books</option>
                                                <option value="Exam Preparation" {{ $allInfo['genre'] == 'Exam Preparation' ? 'selected' : '' }}>Exam Preparation</option>
                                                <option value="Health, Family & Personal Development" {{ $allInfo['genre'] == 'Health, Family & Personal Development' ? 'selected' : '' }}>Health, Family & Personal Development</option>
                                                <option value="Health, Fitness & Nutrition" {{ $allInfo['genre'] == 'Health, Fitness & Nutrition' ? 'selected' : '' }}>Health, Fitness & Nutrition</option>
                                                <option value="Historical Fiction" {{ $allInfo['genre'] == 'Historical Fiction' ? 'selected' : '' }}>Historical Fiction</option>
                                                <option value="History" {{ $allInfo['genre'] == 'History' ? 'selected' : '' }}>History</option>
                                                <option value="Humour" {{ $allInfo['genre'] == 'Humour' ? 'selected' : '' }}>Humour</option>
                                                <option value="Language, Linguistics & Writing" {{ $allInfo['genre'] == 'Language, Linguistics & Writing' ? 'selected' : '' }}>Language, Linguistics & Writing</option>
                                                <option value="Law" {{ $allInfo['genre'] == 'Law' ? 'selected' : '' }}>Law</option>
                                                <option value="Literature & Fiction" {{ $allInfo['genre'] == 'Literature & Fiction' ? 'selected' : '' }}>Literature & Fiction</option>
                                                <option value="Maps & Atlases" {{ $allInfo['genre'] == 'Maps & Atlases' ? 'selected' : '' }}>Maps & Atlases</option>
                                                <option value="Medicine & Health Sciences" {{ $allInfo['genre'] == 'Medicine & Health Sciences' ? 'selected' : '' }}>Medicine & Health Sciences</option>
                                                <option value="Politics" {{ $allInfo['genre'] == 'Politics' ? 'selected' : '' }}>Politics</option>
                                                <option value="Reference" {{ $allInfo['genre'] == 'Reference' ? 'selected' : '' }}>Reference</option>
                                                <option value="Religion & Spirituality" {{ $allInfo['genre'] == 'Religion & Spirituality' ? 'selected' : '' }}>Religion & Spirituality</option>
                                                <option value="Science & Mathematics" {{ $allInfo['genre'] == 'Science & Mathematics' ? 'selected' : '' }}>Science & Mathematics</option>
                                                <option value="Science Fiction & Fantasy" {{ $allInfo['genre'] == 'Science Fiction & Fantasy' ? 'selected' : '' }}>Science Fiction & Fantasy</option>
                                                <option value="Sports" {{ $allInfo['genre'] == 'Sports' ? 'selected' : '' }}>Sports</option>
                                                <option value="Teen & Young Adult" {{ $allInfo['genre'] == 'Teen & Young Adult' ? 'selected' : '' }}>Teen & Young Adult</option>
                                                <option value="Textbooks & Study Guides" {{ $allInfo['genre'] == 'Textbooks & Study Guides' ? 'selected' : '' }}>Textbooks & Study Guides</option>
                                                <option value="Travel & Tourism" {{ $allInfo['genre'] == 'Travel & Tourism' ? 'selected' : '' }}>Travel & Tourism</option>
                                            </select>
                                            <!-- <input id="genre" type="genre" class="form-control @error('genre') is-invalid @enderror" name="genre" value="{{ $allInfo['genre']??'Books' }}" autocomplete="genre" autofocus placeholder="Genre"> -->
                                            <span class="error-message genre" style="color:red;"></span>

                                            @error('genre')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="manufacturer" class="form-label d-flex justify-content-between">{{ __('Manufacturer') }}</label>
                                            <input style="background-color: #e9f85c;" id="manufacturer" type="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" name="manufacturer" value="{{ $allInfo['manufacturer']??'As Per Publisher' }}" autocomplete="manufacturer" autofocus placeholder="Manufacturer">
                                            <span class="error-message manufacturer" style="color:red;"></span>

                                            @error('manufacturer')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="importer" class="form-label d-flex justify-content-between">{{ __('Importer') }}</label>
                                            <input style="background-color: #e9f85c;" id="importer" type="importer" class="form-control @error('importer') is-invalid @enderror" name="importer" value="{{ $allInfo['importer']??'Not Applicable' }}" autocomplete="importer" autofocus placeholder="Importer">
                                            <span class="error-message importer" style="color:red;"></span>

                                            @error('importer')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="">
                                            <label for="packer" class="form-label d-flex justify-content-between">{{ __('Packer') }}</label>
                                            <input style="background-color: #e9f85c;" id="packer" type="packer" class="form-control @error('packer') is-invalid @enderror" name="packer" value="{{ $allInfo['packer']??'Fullfilled by Supplier' }}" autocomplete="packer" autofocus placeholder="Packer">
                                            <span class="error-message packer" style="color:red;"></span>

                                            @error('packer')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Insta Mojo URL -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="url" class="form-label d-flex justify-content-between">
                                                {{ __('Insta Mojo URL') }}
                                                <span onclick="copyLink()" id="copylink" style="cursor:pointer;">Copy</span>
                                            </label>
                                            <input id="url" type="url" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url') ?? $allInfo['url'] }}" autocomplete="url" autofocus placeholder="Insta Mojo URL">
                                            <span class="error-message url" style="color:red;"></span>
                                            @error('url')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Base Image URL -->
                                    <div class="col-md-4">
                                        <div>
                                            <label for="base_url" class="form-label">{{ __('Base Image URL') }}</label>
                                            <input id="base_url" type="text" class="form-control @error('images') is-invalid @enderror" name="images[]" value="{{ old('images') ?? $allInfo['baseimg'] }}" autocomplete="images" autofocus placeholder="Base URL">
                                            <span class="error-message images" style="color:red;"></span>
                                            @error('images')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <button type="button" id="addFileInput" class="btn btn-primary mt-2">Add More Images</button>
                                    </div>

                                    <!-- Additional Images -->
                                    @foreach($allInfo['multiple'] as $key => $images)
                                    @if($key == 0) @continue; @endif
                                    <div class="col-md-4">
                                        <div>
                                            <label class="form-label">{{ __('Additional Image URL') }}</label>
                                            <div class="input-group align-items-center">
                                                <input type="text" class="form-control @error('multipleImages') is-invalid @enderror" name="multipleImages[]" value="{{ old('multipleImages') ?? $images }}" autocomplete="multipleImages" autofocus placeholder="Additional Image URL">
                                                <div class="input-group-append">
                                                    <img src="/assets/images/cross.png" class="removeFileInput" style="width:25px;margin-left:5px; cursor:pointer;" />
                                                </div>
                                            </div>
                                            <span class="error-message multipleImages" style="color:red;"></span>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                            </div>

                            <!-- Preview Section -->
                            <div class="col-md-3 text-right mb-2 d-flex" id='preview' style="justify-content: flex-end;">
                                <div style="border: 2px solid #ccc;width: 300px;max-height: 300px;height:300px;">
                                    <img src="{{ old('images') ?? $allInfo['baseimg'] }}" id='previewImage' />
                                    <div class='image-status' style="text-align: center;padding: 5px;display:none;"></div>
                                </div>
                            </div>
                        </div>


                        <div style="text-align: right;margin-top: 30px;">
                            <button type="submit" class="btn btn-success float-right">Update & Publish</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <div id="fileInputContainer">
                            <div class="form-group">
                                <label for="fileInput1">Main Images<span class="text-danger">*</span></label>
                                <div class="form-group mb-0" @error('images') style="border: red 2px dotted;" @enderror>
                                    <input type="hidden" name="images[]" value={{ $allInfo['baseimg'] }} />
                                    <input type="file" class="dropify @error('images') is-invalid @enderror" data-bs-height="180" id="fileInput1" name="images[]" value={{ $allInfo['baseimg'] }} data-default-file={{$allInfo['baseimg']}} />
                                </div>
                                <a href="{{route('process.image')}}?url={{$allInfo['baseimg']}}" download="image.jpg" class="w-100 d-flex justify-content-end my-4"><img src="/downlod-icon.png" /></a>

                                @error('images')
                                <span class="invalid-feedback mt-2" style="display:block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror

                                <div id='fileInputContainer'>
                                    @if($allInfo['multiple'] && count($allInfo['multiple']) != 1)
                                    <label for="fileInput1" class="mt-2">Preview Images<span class="text-danger">*</span></label><br>
                                    @foreach($allInfo['multiple'] as $key => $images)
                                    @if($key == 0) @continue; @endif
                                    <div class="input-group{{$key}}">
                                        <input type="hidden" name="multipleImages[]" value={{ $images }} />
                                        <div class="form-group mb-1" @error('multipleImages') style="border: red 2px dotted;" @enderror>
                                            <input data-default-file={{$images}} id="demo" type="file" class="dropify @error('multipleImages') is-invalid @enderror" name="multipleImages[]" multiple>
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="multipleImages[]" value="{{$images}}" />
                                                <div class="input-group-append pt-2"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <button class="btn btn-danger removeFileInput mb-3" id='{{$key}}'>Remove</button>
                                            <a href='{{route("process.image")}}?url={{$images}}' download="image.jpg"><img src="/downlod-icon.png" /></a>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>

                                <label for="fileInput1" class="mb-0">Images<span class="text-danger">*</span>( Multiple Images )</label>
                                <div class="form-group mt-2" @error('multipleImages') style="border: red 2px dotted;" @enderror>
                                    <input id="demo" type="file" class="dropify @error('multipleImages') is-invalid @enderror" name="multipleImages[]" multiple>
                                    <form action="{{ route('convert.image') }}" method="post" enctype="multipart/form-data" id='multipleImagesform'>
                                        @csrf
                                        <input id="multipleFiles" type="file" class="dropify @error('multipleImages') is-invalid @enderror" name="multipleImages[]" multiple>
                                        <div id='multiImagesDownload' style="display: none;">
                                            <a href='#' class="w-100 d-flex justify-content-end my-4" id='downloadMultipleImage'>
                                                <img src="/downlod-icon.png" />
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                @error('multipleImages')
                                <span class="invalid-feedback mt-2" style="display:block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  -->
    </div>
    <!-- End Row -->
</div>
@endsection

@push('js')
<script src="{{ asset('assets/plugins/fileuploads/js/fileupload.js') }}"></script>
<script src="{{ asset('assets/plugins/fileuploads/js/file-upload.js') }}"></script>
<!-- INTERNAL File-Uploads Js-->
<script src="{{ asset('assets/plugins/fancyuploder/jquery.ui.widget.js') }}"></script>
<script src="{{ asset('assets/plugins/fancyuploder/jquery.fileupload.js') }}"></script>
<script src="{{ asset('assets/plugins/fancyuploder/jquery.fancy-fileupload.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $("#multipleFiles").change(function() {
            $('#multiImagesDownload').show();
        })

        $("#downloadMultipleImage").click(function() {
            $("#multipleImagesform").submit();
        })

        $('#publishDB').click(function() {
            $("#form").attr('action', "{{ route('listing.publish.database', $post->id??$productId) }}");

            $("#form").submit();
        })

        $(".duplicate_listing").click(function(e) {
            e.preventDefault();
            $("#form").attr("action", "{{ route('listing.publish.database', $post->id) }}");
            $("#form").append("<input type='hidden' name='duplicate' value=1 />");
            $("input[name=_method]").val("POST");
            $("#form").submit();
        });
    });
</script>

@include('listing.script')

@endpush