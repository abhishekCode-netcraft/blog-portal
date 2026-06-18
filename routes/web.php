<?php

use App\Http\Controllers\WatermarkController;
use App\Http\Controllers\CollageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FulfilmentTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminComplaintController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\RoleController;
use App\Services\GoogleService;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BackupListingsController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\CandidatesController;
use App\Http\Controllers\DatabaseListingController;
use App\Http\Controllers\ImageMakerController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GraphicalDashboardController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SubSubCategoryController;
use App\Http\Controllers\ListingModifyController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PromotionalController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Controllers\OnDemandListingController;
use App\Http\Controllers\IssueTypeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\PublicComplaintController;
use App\Http\Controllers\QrResourceController;

Illuminate\Support\Facades\Auth::routes();

/*
 |--------------------------------------------------------------------------
 | Web Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register web routes for your application. These
 | routes are loaded by the RouteServiceProvider and all of them will
 | be assigned to the "web" middleware group. Make something great!
 |
 */
Route::post('/auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/auth/google/refresh', [GoogleController::class, 'refreshGoogle'])
    ->name('google.refresh.token');

Route::match(['get', 'post'], '/verify/otp', [LoginController::class, 'authenticateOTP'])
    ->name('verify.otp');

Route::get('assets/{id}', \App\Http\Controllers\ImageMakerController::class)
    ->name('assets');

Route::get('register/otp', [UserController::class, 'registerOTP'])
    ->name('register.otp');

Route::post('registers/store', [UserController::class, 'register'])
    ->name('register.store');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'web']], function () {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get(
        'dashboard/analytics',
        [GraphicalDashboardController::class, 'index']
    )->name('graphical.dashboard')->middleware('auth');

    Route::get('posts/count', [DashboardController::class, 'getStats'])
        ->name('get.posts.count');

    Route::get('term/condition', [SettingsController::class, 'getTermCondition'])
        ->name('get.term_condition');

    Route::get('policies', [SettingsController::class, 'getPolicies'])
        ->name('get.policies');

    /**
     * Profile
     */
    Route::group(
        ['prefix' => 'profile'],
        function () {
            Route::get('', [ProfileController::class, 'edit'])
                ->name('profile.edit');

            Route::get('listings', [ProfileController::class, 'listings'])
                ->name('profile.listing');

            Route::post('', [ProfileController::class, 'update'])
                ->name('profile.update');

            Route::post('delete', [ProfileController::class, 'delete'])
                ->name('profile.listing.delete');

            Route::get('delete/{id}', [ProfileController::class, 'singleDelete'])
                ->name('profile.listing.delete.single');
        }
    );

    /**
     * Profile
     */

    /**
     * Inventory
     */
    Route::group(
        ['prefix' => 'inventory'],
        function () {
            Route::get('', [ListingController::class, 'inventory'])
                ->name('inventory.index');

            Route::get('review', [ListingController::class, 'reviewInventory'])
                ->name('inventory.review');

            Route::get('review/price/issue', [DatabaseListingController::class, 'reviewPriceIssue'])
                ->name('inventory.review.price.issue');

            Route::get('drafted', [ListingController::class, 'draftedInventory'])
                ->name('inventory.drafted');
        }
    );
    /**
     * Inventory
     */

    /**
     * Image related
     */
    Route::group(
        ['prefix' => 'images'],
        function () {
            Route::get('single/create', [ImageMakerController::class, 'singleImage'])->name('image.single.create');

            Route::get('combo/create', [ImageMakerController::class, 'comboImage'])->name('image.combo.create');

            Route::get('gallery', [ImageMakerController::class, 'imageGallery'])->name('image.gallery');

            Route::get('gallery/delete', [ImageMakerController::class, 'deleteImage'])->name('image.gallery.delete');

            Route::get('refresh', [ImageMakerController::class, 'refreshURL'])->name('image.url.refresh');

            Route::post('watermark/store', [WatermarkController::class, 'store'])->name('image.watermark.store');

            Route::post('collage/store', [CollageController::class, 'store'])->name('image.collage.store');
        }
    );

    /**
     * Modify Listing
     */
    Route::group(
        ['prefix' => 'modify-listing'],
        function () {
            Route::get('', [ListingModifyController::class, 'index'])->name('modify-listing.index');
            Route::get('requested', [ListingModifyController::class, 'requested'])->name('modify-listing.requested');
            Route::get('approval', [ListingModifyController::class, 'approval'])->name('modify-listing.approval');
            Route::get('fetch-product', [ListingModifyController::class, 'fetchProduct'])->name('modify-listing.fetch');
            Route::post('store', [ListingModifyController::class, 'store'])->name('modify-listing.store');
            Route::get('download-sample', [ListingModifyController::class, 'downloadSample'])->name('modify-listing.sample');
            Route::post('upload-excel', [ListingModifyController::class, 'uploadExcel'])->name('modify-listing.upload');
            Route::delete('/modify-listing/{id}', [ListingModifyController::class, 'destroy'])
                ->name('modify-listing.delete');
        }
    );

    Route::group(
        ['prefix' => 'marketplace'],
        function () {
            Route::get('calculation', [\App\Http\Controllers\MarketPlaceController::class, 'index'])->name('marketplace.calculation');
            Route::post('calculate', [\App\Http\Controllers\MarketPlaceController::class, 'calculate'])->name('marketplace.calculate');
            Route::post('get-book-types', [\App\Http\Controllers\MarketPlaceController::class, 'getBookTypes'])->name('marketplace.get_book_types');
            Route::post('get-discount', [\App\Http\Controllers\MarketPlaceController::class, 'getDiscount'])->name('marketplace.get_discount');
            Route::post('get-weight-charges', [\App\Http\Controllers\MarketPlaceController::class, 'getWeightCharges'])->name('marketplace.get_weight_charges');
        }
    );

    /**
     * On Demand Listing
     */
    Route::group(
        ['prefix' => 'on-demand-listing'],
        function () {
            Route::get('', [OnDemandListingController::class, 'index'])->name('on-demand.index');
            Route::post('store', [OnDemandListingController::class, 'store'])->name('on-demand.store');
            Route::get('verify', [OnDemandListingController::class, 'verify'])->name('on-demand.verify');
            Route::post('complete', [OnDemandListingController::class, 'complete'])->name('on-demand.complete');
            Route::post('uncomplete', [OnDemandListingController::class, 'uncomplete'])->name('on-demand.uncomplete');
            Route::get('download/{id}', [OnDemandListingController::class, 'download'])->name('on-demand.download');
            Route::post('bulk-download', [OnDemandListingController::class, 'bulkDownload'])->name('on-demand.bulk-download');
            Route::post('bulk-delete', [OnDemandListingController::class, 'bulkDelete'])->name('on-demand.bulk-delete');
            Route::post('bulk-transfer', [OnDemandListingController::class, 'bulkTransfer'])->name('on-demand.bulk-transfer');
            Route::get('logs', [OnDemandListingController::class, 'logs'])->name('on-demand.logs');
        }
    );

    /**
     * Image
     */

    /**
     * Settings
     */
    Route::group(
        ['prefix' => 'settings'],
        function () {
            Route::get('blog', [SettingsController::class, 'blog'])
                ->name('settings.blog');

            Route::get('site', [SettingsController::class, 'site'])
                ->name('settings.site');

            Route::post('update/site', [SettingsController::class, 'update'])
                ->name('settings.site.update');

            Route::get('policies/term', [SettingsController::class, 'termNcondition'])
                ->name('settings.policies');

            Route::post('policies/term', [SettingsController::class, 'saveAndUpdateTermNcondition'])
                ->name('settings.policies.save');

            Route::group(
                ['prefix' => 'keywords'],
                function () {
                    Route::get('validate', [SettingsController::class, 'FieldsValidate'])
                        ->name('settings.keywords.validate');

                    Route::get('validations', [SettingsController::class, 'fieldsValidations'])
                        ->name('settings.keywords.valid');

                    Route::post('validations', [SettingsController::class, 'keywordsNotAllowed'])
                        ->name('settings.keywords.notallowed');

                    Route::get('delete/{id}', [SettingsController::class, 'keywordsDelete'])
                        ->name('settings.keywords.delete');

                    Route::post('update/{id}', [SettingsController::class, 'updateKeywords'])
                        ->name('settings.keywords.update');
                }
            );

            Route::get('city-cost/sample', [SettingsController::class, 'downloadCityCostSample'])
                ->name('settings.city_cost.sample');

            Route::get('marketplace-commission/sample', [SettingsController::class, 'downloadMarketplaceCommissionSample'])
                ->name('settings.marketplace_commission.sample');
        }
    );

    Route::group(
        ['prefix' => 'fulfilment-types'],
        function () {
            Route::get('/', [FulfilmentTypeController::class, 'index'])->name('fulfilment.index');
            Route::post('store', [FulfilmentTypeController::class, 'store'])->name('fulfilment.store');
            Route::post('update/{id}', [FulfilmentTypeController::class, 'update'])->name('fulfilment.update');
            Route::get('delete/{id}', [FulfilmentTypeController::class, 'destroy'])->name('fulfilment.delete');
        }
    );
    /**
     * Settings ENd
     */

    /**
     * Backup
     */
    Route::group(
        ['prefix' => 'backup'],
        function () {
            Route::get('listings', [BackupListingsController::class, 'backupListings'])
                ->name('backup.listings');

            Route::get('logs', [BackupListingsController::class, 'getLoggerFile'])
                ->name('backup.logs');

            Route::post('run/backup', [BackupListingsController::class, 'manuallyRunBackup'])
                ->name('backup.run.backup');

            Route::get('queues', [BackupListingsController::class, 'getQueues'])
                ->name('get.queues');

            Route::get('emails', [BackupListingsController::class, 'backupEmail'])
                ->name('settings.emails');

            Route::post('emails', [BackupListingsController::class, 'saveEmail'])
                ->name('settings.emails.save');

            Route::get('delete/emails/{id}', [BackupListingsController::class, 'deleteEmail'])
                ->name('backup.emails.delete');

            Route::get('manually', [BackupListingsController::class, 'manuallyRunBackup'])
                ->name('manually.backup');

            Route::get('dropbox', [BackupListingsController::class, 'dropBox']);
            Route::post('dropbox/submit', [BackupListingsController::class, 'uploadfile'])->name('upload.file');
        }
    );

    Route::get('export', [BackupListingsController::class, 'export'])
        ->name('backup.export');

    /**
     * Backup
     */

    /**
     * Google Services
     */
    Route::group(
        ['prefix' => 'google/products'],
        function () {
            Route::get('list', [GoogleController::class, 'listProducts'])
                ->name('google.products.list');
        }
    );

    Route::match(['get', 'post'], 'process/image', [GoogleService::class, 'processImageAndDownload'])
        ->name('process.image');

    Route::post('convert/image', [GoogleService::class, 'downloadProcessedImage'])
        ->name('convert.image');

    Route::post('drafted/posts', [GoogleService::class, 'draftedInventory'])
        ->name('drafted.posts');

    Route::post('live/posts', [GoogleService::class, 'posts'])
        ->name('live.posts');
    /**
     * Google Services
     */

    /**
     * Roles
     */
    Route::resource('roles', RoleController::class);

    Route::get('roles/all/view', [RoleController::class, 'view'])
        ->name('view.roles');
    /**
     * Roles
     */

    /**
     * Direct Blogger
     */
    Route::resource('listing', ListingController::class);

    Route::get('articles', [DatabaseListingController::class, 'articles'])
        ->name('articles.index');

    Route::get('search', [ListingController::class, 'search'])
        ->name('listing.search');

    Route::get('blog/publish/{id}', [ListingController::class, 'publishBlog'])
        ->name('blog.publish');

    Route::get('review/inventory/export', [ListingController::class, 'inventoryReviewExport'])
        ->name('review_inventory_export');
    /**
     * Direct Blogger End
     */

    /**
     * Database
     */
    Route::post('publish/database', [DatabaseListingController::class, 'publshInDB'])
        ->name('listing.publish.database');

    Route::get('publishers', [DatabaseListingController::class, 'getPublisher'])
        ->name('listing.publishers');

    Route::get('publishers/{publisher}', [DatabaseListingController::class, 'getSpecificPublisher'])
        ->name('listing.specific.publishers');

    Route::get('export/publishers', [DatabaseListingController::class, 'export'])
        ->name('listing.publishers.export');

    Route::get('edit/database/{id}', [DatabaseListingController::class, 'editInDB'])
        ->name('listing.edit.database');

    Route::get('copy/database/{id}', [DatabaseListingController::class, 'copyDatabase'])
        ->name('copy_database');

    Route::resource('database-listing', DatabaseListingController::class);

    Route::get('edit/publish/pending/{id}', [DatabaseListingController::class, 'editPublish'])
        ->name('publish.edit');

    Route::get('publish/pending', [DatabaseListingController::class, 'getPublishPending'])
        ->name('publish.pending');

    Route::get('blog/publish/{id}', [ListingController::class, 'publishBlog'])
        ->name('blog.publish');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('update/status', [DatabaseListingController::class, 'updateStatus'])
        ->name('listing.status');

    Route::post('database/tmp', [DatabaseListingController::class, 'previewTemp'])
        ->name('database_temp');

    Route::get('fields/changed/{id}', [DatabaseListingController::class, 'fieldsAreChanged'])
        ->name('database.fields.changed');
    /**
     * Database END
     */

    /** Developers Functionalities */
    Route::prefix('developers')->name('developers.')->group(
        function () {
            Route::get('/', [DeveloperController::class, 'index'])->name('index');
            Route::get('/create', [DeveloperController::class, 'create'])->name('create');
            Route::post('/store', [DeveloperController::class, 'store'])->name('store');
            Route::get('{user}/edit', [DeveloperController::class, 'edit'])->name('edit');
            Route::put('{user}/update', [DeveloperController::class, 'update'])->name('update');
            Route::get('{user}/api-key-gen', [DeveloperController::class, 'keyRegenerate'])->name('api-key-gen');
            Route::delete('{user}/delete', [DeveloperController::class, 'destroy'])->name('destroy');
        }
    );

    /**
     * User Functionalities
     */
    Route::resource('users', UserController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('subcategories', SubCategoryController::class);

    Route::resource('sub-subcategories', SubSubCategoryController::class);

    Route::post('posts/update', [PostsController::class, 'bulkUpdate'])
        ->name('posts.bulk.update');
    Route::get('/batch-details/{id}', [PostsController::class, 'batchDetails'])
        ->name('batch.details');

    Route::delete('posts/delete', [PostsController::class, 'bulkDelete'])
        ->name('posts.bulk.delete');

    Route::post('posts/{id}/single-update', [PostsController::class, 'updateSingle'])
        ->name('posts.customupdate');

    Route::delete('posts/{id}', [PostsController::class, 'destroy'])
        ->name('posts.destroy');

    Route::resource('posts', PostsController::class)
        ->except(['destroy', 'update']);

    Route::resource('content', ContentController::class)->only([
        'index',
        'create',
        'store'
    ]);

    Route::get('content/row', [ContentController::class, 'getRow'])
        ->name('content.row');

    // Page से Content create page पर redirect
    Route::get('/content/from-page/{id}', [ContentController::class, 'createFromPage'])
        ->name('content.fill.from.page');

    Route::get('approval/list', [ContentController::class, 'approvalList'])
        ->name('approval.list');

    Route::resource('pages', PageController::class);

    Route::resource('promotional', PromotionalController::class)->only([
        'index',
        'create',
        'store'
    ]);

    Route::post('approval/submit', [PromotionalController::class, 'submit'])
        ->name('approval.submit');

    Route::post('approval/quick-update', [PromotionalController::class, 'quickUpdate'])
        ->name('approval.quick.update');

    Route::get('promotional/row', [PromotionalController::class, 'getRow'])
        ->name('promotional.row');

    Route::resource('worktype', WorkTypeController::class);

    Route::resource('issue-types', IssueTypeController::class);

    Route::resource('official-issue-types', IssueTypeController::class);

    Route::resource('departments', DepartmentController::class);

    Route::resource('official-departments', DepartmentController::class);

    Route::get('count/users', [UserController::class, 'userCounts'])
        ->name('users.count');

    Route::get('change/password', [UserController::class, 'updatePassword'])
        ->name('change.user.password');

    Route::post('update/password', [UserController::class, 'updatePassword'])
        ->name('update.user.password');

    Route::get('users/verified/approved', [UserController::class, 'verified'])
        ->name('verified.users');

    Route::get('edit/users/status/{id}', [UserController::class, 'editStatus'])
        ->name('edit.users.status');

    Route::post('update/users/status/{id}', [UserController::class, 'updateStatus'])
        ->name('update.users.status');

    Route::match(['get', 'post'], 'process/image', [GoogleService::class, 'processImageAndDownload'])
        ->name('process.image');

    Route::post('convert/image', [GoogleService::class, 'downloadProcessedImage'])
        ->name('convert.image');

    Route::post('drafted/posts', [GoogleService::class, 'draftedInventory'])
        ->name('drafted.posts');

    Route::post('live/posts', [GoogleService::class, 'posts'])
        ->name('live.posts');

    // Route::get('posts', [DashboardController::class, 'getStats'])
    //     ->name('get.posts.count');

    Route::get('set/session/id', [UserController::class, 'setSessionId'])
        ->name('user.session.id');

    /**
     * Upload Section
     */
    Route::get('upload-file', [BulkUploadController::class, 'getOptions'])
        ->name('upload-file.options');

    Route::get('download-url', [BulkUploadController::class, 'downloadImage']);

    Route::get('edit/bulk/listing/{id}', [BulkUploadController::class, 'edit'])
        ->name('bulklisting.edit');

    Route::post('update/bulk/listing', [BulkUploadController::class, 'update'])
        ->name('bulklisting.update');

    Route::post('get-upload-file', [BulkUploadController::class, 'import'])
        ->name('get-upload-file.options');

    Route::post('import/data', [BulkUploadController::class, 'importData'])
        ->name('import.data');

    Route::get('view/uploaded/data', [BulkUploadController::class, 'viewUploadedFile'])
        ->name('view.upload');

    Route::get('delete/uploaded/data', [BulkUploadController::class, 'delete'])
        ->name('delete.upload.data');

    Route::get('/getpublishernames', [PublicationController::class, 'getpublishers'])
        ->name('getpublishernames');

    Route::get('/getpublications', [PublicationController::class, 'getpublications'])
        ->name('getpublications');

    Route::get('/publication/details', [PublicationController::class, 'details'])
        ->name('get.publication.details');

    Route::get('/get/ai/description', [ChatGptController::class, 'openAi'])
        ->name('ai.description');

    Route::post('/get/ai/description', [ChatGptController::class, 'responseAiDescription'])
        ->name('getai.response');

    Route::post('/get/ai/descriptionfetcher', [ChatGptController::class, 'responseAidescriptionfetcher'])
        ->name('getai.descriptionfetcher');


    Route::post('/support/mail', [HomeController::class, 'supportMail'])
        ->name('support.mail');


    /**
     *  END
     */
    Route::prefix('candidates')->name('candidates.')->group(
        function () {
            Route::get('enquiries', [CandidatesController::class, 'enquiries'])
                ->name('enquiries');

            Route::post('enquiries/{id}/save-note', [CandidatesController::class, 'saveNotes'])
                ->name('enquiries.saveNotes');

            Route::post('enquiries/{id}/update', [CandidatesController::class, 'updateStatus'])
                ->name('enquiries.status.update');


            Route::get('/candidates/export', [CandidatesController::class, 'export'])
                ->name('enquiries.export');
        }
    );

    Route::resource('qr-resource', QrResourceController::class);

    Route::get('download-qr-resource', [QrResourceController::class, 'downloadSingleQR'])
        ->name('qr-resource.single.download');

    Route::get('qr-codes/{id}/download', [QrResourceController::class, 'download'])
        ->name('qr-resource.download');

    Route::get('review', [ReviewController::class, 'index'])
        ->name('review.index');

    Route::post('/review/{reviewUser}/status', [
        ReviewController::class,
        'updateStatus'
    ])->name('review.status');

    Route::delete(
        '/reviews/{reviewUser}',
        [ReviewController::class, 'destroy']
    )->name('review.destroy');
});

Route::get('qr/{slug}', [QrResourceController::class, 'redirect'])
    ->name('qr-resource.redirect');

Route::get('authorised-distributor', [DistributionController::class, 'index'])
    ->name('distribution.index');

Route::get('our-distribution', [DistributionController::class, 'index']);

Route::group(['prefix' => 'password'], function () {
    Route::get('reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

    Route::post('email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

    Route::post('reset', [ResetPasswordController::class, 'reset']);

    Route::post('update', [ResetPasswordController::class, 'updatePassword'])->name('password.update');
});

Route::get('getPriceRecords', [ListingController::class, 'getPriceRecords'])
    ->name('listing.getPriceRecords');

Route::get('/check-session-status', function () {
    session()->put('expire_error', 'Your session has expired. Please log in again.');
    return redirect()->route('login');
})->name('check.session');

Route::get('delete/session/{id}', [UserController::class, 'deleteSessionId'])
    ->name('user.session.delete');

Route::get('/assets/images/brand/{filename}', UserController::class)
    ->name('asset.name');

Route::get('', [HomeController::class, 'index'])
    ->name('home');



Route::match(['get', 'post'], 'price/calculation', [UserController::class, 'priceCalculation'])
    ->name('price.calculation');

Route::resource('complaints-user', ComplaintController::class);

Route::group(['prefix' => 'complaints'], function () {
    Route::get('start', [PublicComplaintController::class, 'startVerification'])
        ->name('public.complaints.verify.start');

    Route::get('create', [PublicComplaintController::class, 'create'])
        ->name('public.complaints.create');

    Route::post('send-otp', [PublicComplaintController::class, 'sendOtp'])
        ->name('public.complaints.sendOtp');

    Route::post('verify-otp', [PublicComplaintController::class, 'verifyOtp'])
        ->name('public.complaints.verifyOtp');

    Route::get('dashboard', [PublicComplaintController::class, 'dashboard'])
        ->name('public.complaints.dashboard');

    Route::get('list', [PublicComplaintController::class, 'index'])
        ->name('public.complaints.index');

    Route::get('view/{id}', [PublicComplaintController::class, 'show'])
        ->name('public.complaints.show');

    Route::post('reply/{id}', [PublicComplaintController::class, 'storeReply'])
        ->name('public.complaints.reply');

    Route::post('store', [PublicComplaintController::class, 'store'])
        ->name('public.complaints.store');

    Route::get('success/{ticket_id}', [PublicComplaintController::class, 'success'])
        ->name('public.complaints.success');
});

// Admin Complaint Routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::group(
        ['prefix' => 'complaints'],
        function () {
            Route::get('', [AdminComplaintController::class, 'index'])
                ->name('admin.complaints.index');

            Route::get('create', [AdminComplaintController::class, 'create'])
                ->name('admin.complaints.create');

            Route::post('store', [AdminComplaintController::class, 'store'])
                ->name('admin.complaints.store');

            Route::get('{id}', [AdminComplaintController::class, 'show'])
                ->name('admin.complaints.show');

            Route::post('{id}/reply', [AdminComplaintController::class, 'storeReply'])
                ->name('admin.complaints.reply');
        }
    );

    Route::group(['prefix' => 'official-complaints'], function () {
        Route::get('', [AdminComplaintController::class, 'officialIndex'])
            ->name('admin.official-complaints.index');

        Route::get('create', [AdminComplaintController::class, 'officialCreate'])
            ->name('admin.official-complaints.create');

        Route::post('store', [AdminComplaintController::class, 'officialStore'])
            ->name('admin.official-complaints.store');

        Route::get('{id}', [AdminComplaintController::class, 'officialShow'])
            ->name('admin.official-complaints.show');

        Route::post('{id}/reply', [AdminComplaintController::class, 'storeReply'])
            ->name('admin.official-complaints.reply');
    });
});
