<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BlogRequest;
use App\Jobs\PublishProducts;
use App\Jobs\UpdateProducts;
use App\Models\BackupListing;
use App\Models\Job;
use App\Models\Listing;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserListingCount;
use App\Models\UserListingInfo;
use Illuminate\Support\Facades\Http;
use App\Services\GoogleService;
use Carbon\Carbon;
use App\Models\WeightVSCourier;
use Auth;
use DB;

class DatabaseListingController extends Controller
{
    /**
     * Constructor
     *
     * @param GoogleService $googleService
     */
    public function __construct(protected GoogleService $googleService)
    {
        $this->middleware('role_or_permission:Listing create ( DB )', ['only' => ['create']]);
        $this->middleware('role_or_permission:RA-Pending Listing (DB)', ['only' => ['index']]);
        $this->middleware('role_or_permission:RA-Updated Listings (MS)', ['only' => ['getPublishPending']]);
    }

    /**
     * Display the listing of the resources
     */
    public function index(Request $request)
    {
        $googlePosts = Listing::with('created_by_user')
            ->orderBy('created_at', 'desc')
            ->where('categories', 'LIKE', '%' . request()->category . '%')
            ->where('is_bulk_upload', 0)
            ->whereNull('product_id');

        if (request()->user != 'all') {
            $googlePosts = $googlePosts->where('created_by', request()->user);
        }

        if (request()->status) $googlePosts = $googlePosts->where('status', request()->status);

        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
            $googlePosts = $googlePosts->where('created_by', auth()->user()->id);
        }
        $googlePosts = $googlePosts->paginate($request->paging);
        $allCounts = Listing::whereNull('product_id')->where('is_bulk_upload', 0)->count();

        $pendingCounts = Listing::where('status', 0)->where('is_bulk_upload', 0)
            ->whereNull('product_id');

        $rejectedCounts = Listing::where('status', 2)->where('is_bulk_upload', 0)
            ->whereNull('product_id');

        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
            $pendingCounts = $pendingCounts->where('created_by', auth()->user()->id);

            $rejectedCounts = $rejectedCounts->where('created_by', auth()->user()->id);
        }

        $pendingCounts = $pendingCounts->count();

        $rejectedCounts = $rejectedCounts->count();

        $users = User::where('status', 1)->get();

        return view('database-listing.index', compact('googlePosts', 'allCounts', 'pendingCounts', 'rejectedCounts', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$url = $this->getSiteBaseUrl()) {
            session()->flash('message', 'Please complete your Site Setting Then Continue');

            return view('settings.error');
        }

        $response = Http::withoutVerifying()->get($url . '/feeds/posts/default?alt=json');

        $categories = $response->json()['feed']['category'];

        $publications  = WeightVSCourier::all();

        $siteSetting = SiteSetting::first();

        $user_data_transfer = Auth::user()->data_transfer;

        if (!$user_data_transfer) {
            return view('database-listing.create_tmp', compact('categories', 'siteSetting', 'user_data_transfer'));
        } else {
            return view('database-listing.create', compact('categories', 'siteSetting', 'user_data_transfer', 'publications'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request)
    {
        try {
            $listing = $this->listingExist($request->title, $request->description);

            if ($listing) {
                session()->flash('error', '⚠️ Your Title is too similar to existing product');
                return redirect()->back();
            }

            $data = [
                '_token' => $request->_token,
                'title' => $request->title,
                'description' => $request->description,
                'mrp' => $request->mrp,
                'selling_price' => $request->selling_price,
                'publisher' => $request->publication,
                'author_name' => $request->author_name,
                'edition' => $request->edition,
                'categories' => $request->label,
                'sku' => $request->sku,
                'language' => $request->language,
                'no_of_pages' => $request->pages,
                'condition' => $request->condition,
                'binding_type' => $request->binding,
                'insta_mojo_url' => $request->url,
                'images' => json_encode($request->images),
                'multiple_images' => $request->multipleImages,
                'status' => 0,
                'created_by' => auth()->user()->id,
                'isbn_10' => $request->isbn_10,
                'isbn_13' => $request->isbn_13,
                'publish_year' => $request->publish_year,
                'weight' => $request->weight,
                'reading_age' => $request->reading_age,
                'country_origin' => $request->country_origin,
                'genre' => $request->genre,
                'manufacturer' => $request->manufacturer,
                'importer' => $request->importer,
                'packer' => $request->packer,
            ];

            $listing = Listing::create($data);

            UserListingInfo::create([
                'image' => $request->images[0],
                'title' => $request->title,
                'created_by' => auth()->user()->id,
                'approved_by' => null,
                'approved_at' => null,
                'status' => 0,
                'status_listing' => 'Created',
                'listings_id' => $listing->id,
            ]);

            $this->updateTheCount('Created', 'create_count');

            if ($listing) {
                session()->flash('success', 'Listing created successfully');

                return redirect()->back();
            }

            session()->flash('error', 'Someting went wrong');

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('error', 'Something went Wrong!!');

            return redirect()->back();
        }
    }


    public function previewTemp(Request $request)
    {
        try {
            $data = [
                '_token' => $request->_token,
                'title' => $request->title,
                'description' => $request->description,
                'mrp' => $request->mrp,
                'selling_price' => $request->selling_price,
                'publisher' => $request->publication,
                'author_name' => $request->author_name,
                'edition' => $request->edition,
                'categories' => $request->label,
                'sku' => $request->sku,
                'language' => $request->language,
                'no_of_pages' => $request->pages,
                'condition' => $request->condition,
                'binding_type' => $request->binding,
                'insta_mojo_url' => $request->url,
                'images' => $request->images[0],
                'multiple_images' => $request->multipleImages,
                'status' => 0,
                'created_by' => auth()->user()->id,
                'isbn_10' => request()->isbn_10,
                'isbn_13' => request()->isbn_13,
                'publish_year' => request()->publish_year,
                'weight' => request()->weight,
                'reading_age' => request()->reading_age,
                'country_origin' => request()->country_origin,
                'genre' => request()->genre,
                'manufacturer' => request()->manufacturer,
                'importer' => request()->importer,
                'packer' => request()->packer,
            ];

            $publications  = WeightVSCourier::all();

            return view('database-listing.preview', compact('data', 'publications'));
        } catch (\Exception $e) {
            session()->flash('error', 'Something went Wrong!!');
            return redirect()->back();
        }
    }


    public function copyDatabase($id)
    {
        try {
            $findListing = Listing::find($id);

            if (isset($findListing)) {
                $newListing = Listing::create([
                    'title' => $findListing->title,
                    'description' => $findListing->description,
                    'mrp' => $findListing->mrp,
                    'selling_price' => $findListing->selling_price,
                    'publisher' => $findListing->publisher,
                    'author_name' => $findListing->author_name,
                    'edition' => $findListing->edition,
                    'categories' => $findListing->categories,
                    'sku' => $findListing->sku,
                    'language' => $findListing->language,
                    'no_of_pages' => $findListing->no_of_pages,
                    'condition' => $findListing->condition,
                    'binding_type' => $findListing->binding_type,
                    'insta_mojo_url' => $findListing->insta_mojo_url,
                    'images' => $findListing->images,
                    'multiple_images' => $findListing->multiple_images,
                    'product_id' => $findListing->product_id ?? null,
                    'status' => 0,
                    'created_by' => auth()->user()->id,
                    'is_bulk_upload' => $findListing->is_bulk_upload,
                    'isbn_10' => $findListing->isbn_10,
                    'isbn_13' => $findListing->isbn_13,
                    'publish_year' => $findListing->publish_year,
                    'weight' => $findListing->weight,
                    'reading_age' => $findListing->reading_age,
                    'country_origin' => $findListing->country_origin,
                    'genre' => $findListing->genre,
                    'manufacturer' => $findListing->manufacturer,
                    'importer' => $findListing->importer,
                    'packer' => $findListing->packer,
                ]);

                UserListingInfo::create([
                    'image' => $findListing->images,
                    'title' => $findListing->title,
                    'created_by' => auth()->user()->id,
                    'approved_by' => null,
                    'approved_at' => null,
                    'status' => 0,
                    'status_listing' => 'Created',
                    'listings_id' => $newListing->id,
                ]);

                $this->updateTheCount('Created', 'create_count');

                if ($newListing) {
                    session()->flash('success', 'Copy Listing created successfully');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Something went Wrong!!');

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $listing = Listing::find($id);

        $siteSetting = SiteSetting::first();

        if (!$url = $this->getSiteBaseUrl()) {
            session()->flash('message', 'Please complete your Site Setting Then Continue');

            return view('settings.error');
        }

        $response = Http::withoutVerifying()
            ->get($url . '/feeds/posts/default?alt=json');

        $categories = $response->json()['feed']['category'];
        $publications  = WeightVSCourier::all();
        return view('database-listing.edit', compact('listing', 'siteSetting', 'categories', 'publications'));
    }

    /**
     * Update the specified resource in storage.
     */

    private function getMatchingCharacters($str1, $str2)
    {
        $len1 = mb_strlen($str1);
        $len2 = mb_strlen($str2);

        $dp = array_fill(0, $len1 + 1, array_fill(0, $len2 + 1, 0));

        for ($i = 0; $i < $len1; $i++) {
            for ($j = 0; $j < $len2; $j++) {
                if (mb_substr($str1, $i, 1) === mb_substr($str2, $j, 1)) {
                    $dp[$i + 1][$j + 1] = $dp[$i][$j] + 1;
                } else {
                    $dp[$i + 1][$j + 1] = max($dp[$i + 1][$j], $dp[$i][$j + 1]);
                }
            }
        }

        return $dp[$len1][$len2];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, string $id)
    {
        $listing = $this->listingExist($request->title, $request->description);

        if ($listing) {
            session()->flash('error', '⚠️ Your Title is too similar to an existing product. You are not allowed to COPY the title of any existing listing. ');

            return redirect()->back();
        }

        $listing = Listing::findOrFail($id);

        $threshold = 50;
        $wordSim = $this->textSimilarity($request->description, $listing->description);
        $phraseSim = $this->ngramSimilarity($request->description, $listing->description);

        if ($wordSim >= $threshold || $phraseSim >= $threshold) {
            session()->flash('error', "⚠️ Your description is too similar ({$wordSim}% match) to an existing product. You are not allowed to copy any existing listing Description. <br> 
            <span style='color:blue;'><strong>Recommendation:</strong> You should change at least 50% of the description. 
            However, you can COPY the 'Search Key' part.</span>");

            return back();
        }

        $oldStr = strip_tags(
            ($listing->title ?? '') .
                ($listing->description ?? '') .
                ($listing->edition ?? '') .
                (string)$listing->mrp .
                (string)$listing->selling_price
        );

        $newStr = strip_tags(
            ($request->title ?? '') .
                ($request->description ?? '') .
                ($request->edition ?? '') .
                (string)$request->mrp .
                (string)$request->selling_price
        );

        similar_text($oldStr, $newStr, $similarityPercentage);

        $changePercentage = 100 - $similarityPercentage;

        $data = [
            '_token' => $request->_token,
            'title' => $request->title,
            'description' => $request->description,
            'mrp' => $request->mrp,
            'selling_price' => $request->selling_price,
            'publisher' => $request->publication,
            'author_name' => $request->author_name,
            'edition' => $request->edition,
            'categories' => $request->label,
            'sku' => $request->sku,
            'language' => $request->language,
            'no_of_pages' => $request->pages,
            'condition' => $request->condition,
            'binding_type' => $request->binding,
            'insta_mojo_url' => $request->url,
            'images' => json_encode($request->images),
            'multiple_images' => $request->multipleImages,
            'created_by' => auth()->user()->id,
            'isbn_10' => $request->isbn_10,
            'isbn_13' => $request->isbn_13,
            'publish_year' => $request->publish_year,
            'weight' => $request->weight,
            'reading_age' => $request->reading_age,
            'country_origin' => $request->country_origin,
            'genre' => $request->genre,
            'manufacturer' => $request->manufacturer,
            'importer' => $request->importer,
            'packer' => $request->packer,
            'is_bulk_upload' => 0,
            'similarity_percentage' => round($similarityPercentage, 2),
            'change_percentage' => round($changePercentage, 2),
        ];

        $listing = Listing::find($id);

        if ($request->status == 2) {
            if (!request()->price_issue) {
                $this->updateTheCount('Created', 'reject_count');
            }

            $data['status'] = request()->status;

            $additionalInfo = UserListingInfo::where('image', $request->images[0])
                ->where('title', request()->title)
                ->first();

            $additionalInfo->update([
                'status' => request()->status
            ]);
        } else if ($request->status == 1) {
            $additionalInfo = UserListingInfo::where('image', $request->images[0])
                ->where('title', request()->title)
                ->first();

            $additionalInfo->update([
                'status' => request()->status
            ]);
        }

        if ($listing->update($data)) {
            if (request()->modify_id) {
                \App\Models\ListingModifyRequest::where('id', request()->modify_id)->update([
                    'status' => 'Completed',
                    'updated_by' => auth()->id()
                ]);
            }

            session()->flash('success', 'Listing Updated successfully');
            return redirect()->back();
        }

        session()->flash('error', 'Someting went wrong');

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $listing = Listing::find($id);

        UserListingInfo::where('title', $listing->title)
            ->where('image', $listing->images)
            ->delete();

        if ($listing->delete()) {

            if (request()->edit) {
                $this->updateTheCount('Edited', 'delete_count');
            } else {
                $this->updateTheCount('Created', 'delete_count');
            }

            session()->flash('success', 'Listing deleted succesfully.');

            return redirect()->back();
        }

        session()->flash('error', 'Someting went wrong');

        return redirect()->back();
    }

    /**
     * Update Status of Listing
     *
     * @return void
     */
    public function updateStatus()
    {
        // For Publishing and saving to Draft
        if (request()->publish == 3 || request()->publish == 4) {
            foreach (request()->ids as $loopIndex => $id) {
                $job = PublishProducts::dispatch($id, request()->publish, auth()->user()->id)
                    ->delay(now()->addSeconds(10 * $loopIndex));

                $loopIndex++;

                $jobRow = Job::orderBy('id', 'desc')->first();

                if ($jobRow) {
                    Listing::find($id)->update([
                        'job_id' => $jobRow->id,
                        'error' => 'Queued',
                    ]);
                }
            }
        } else if (request()->publish == 5) {
            foreach (request()->ids as $loopIndex => $id) {
                $job = UpdateProducts::dispatch($id, request()->publish, auth()->user()->id)
                    ->delay(now()->addSeconds(10 * $loopIndex));

                $loopIndex++;

                $jobRow = Job::orderBy('id', 'desc')->first();

                if ($jobRow) {
                    Listing::find($id)->update([
                        'job_id' => $jobRow->id,
                        'error' => 'Queued',
                    ]);
                }
            }
        } else if (request()->publish == 6) {
            $listings = Listing::whereIn('id', request()->ids)->get();

            $status = request()->formData[0]['value'];

            foreach ($listings as $listing) {
                $userCount = UserListingCount::where('user_id', $listing->created_by)
                    ->whereDate('created_at', $listing->created_at)
                    ->first();

                if ($status == 6 && !$userCount) {
                    UserListingCount::create([
                        'user_id' => $listing->created_by,
                        'delete_count' => 1,
                    ]);
                } else if ($status == 6 && $userCount) {
                    $userCount->update([
                        'delete_count' => ++$userCount->delete_count,
                    ]);
                }
            }

            Listing::whereIn('id', request()->ids)->delete();
            UserListingInfo::whereIn('listings_id', request()->ids)->delete();
            return response()->json(['success' => true, 'message' => 'Products deleted successfully.']);
        } else {
            $listings = Listing::whereIn('id', request()->formData[1])
                ->get();

            $status = request()->formData[0]['value'];

            foreach ($listings as $listing) {
                $userCount = UserListingCount::where('user_id', $listing->created_by)
                    ->where('created_at', $listing->created_at)
                    ->first();

                if ($status == 2 && !$userCount) {
                    UserListingCount::create([
                        'user_id' => $listing->created_by,
                        'approved_count' => 0,
                        'reject_count' => 1,
                    ]);
                } else if ($status == 2 && $userCount) {
                    $userCount->update([
                        'reject_count' => ++$userCount->reject_count,
                    ]);
                }
            }

            $listings->map(function ($list) use ($status) {
                $additionalInfo = UserListingInfo::where("image", $list->images)
                    ->where('title', $list->title)
                    ->first();

                $additionalInfo->update([
                    'status' => $status
                ]);

                $list->update(['status' => $status]);
            });

            return true;
        }
    }

    /**
     * Get Publish Pending Listing
     *
     * @return void
     */
    public function getPublishPending(Request $request)
    {
        $googlePosts = Listing::with('created_by_user', 'backup_listing')
            ->leftJoin('backup_listings', 'listings.product_id', '=', 'backup_listings.product_id')
            ->orderBy('listings.created_at', 'desc')
            ->whereNotNull('listings.product_id')
            ->where('listings.is_bulk_upload', 0)
            ->select(
                'listings.*',
                'backup_listings.last_updated as backup_last_updated'
            );

        if (request()->has('age_filter')) {
            $filter = request()->age_filter;

            if ($filter === '6_month') {
                $dateFrom = Carbon::now()->subMonths(6);
                $googlePosts->whereBetween('backup_listings.last_updated', [$dateFrom, Carbon::now()]);
            }

            if ($filter === '1_year') {
                $dateFrom = Carbon::now()->subYear();
                $dateTo = Carbon::now()->subMonths(6);
                $googlePosts->whereBetween('backup_listings.last_updated', [$dateFrom, $dateTo]);
            }

            if ($filter === '2_years') {
                $dateFrom = Carbon::now()->subYears(2);
                $dateTo = Carbon::now()->subYear();
                $googlePosts->whereBetween('backup_listings.last_updated', [$dateFrom, $dateTo]);
            }

            if ($filter === '3_years') {
                $dateFrom = Carbon::now()->subYears(3);
                $dateTo = Carbon::now()->subYears(2);
                $googlePosts->whereBetween('backup_listings.last_updated', [$dateFrom, $dateTo]);
            }

            if ($filter === '3_plus_years') {
                $date = Carbon::now()->subYears(3);
                $googlePosts->whereDate('backup_listings.last_updated', '<=', $date);
            }
        }


        $allCounts = Listing::whereNotNull('product_id')
            ->where('is_bulk_upload', 0);

        $pendingCounts = Listing::whereNotNull('product_id')
            ->where('status', 0)
            ->where('is_bulk_upload', 0);

        $rejectedCounts = Listing::whereNotNull('product_id')
            ->where('status', 2)
            ->where('is_bulk_upload', 0);

        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
            $pendingCounts = $pendingCounts->where('created_by', auth()->user()->id);

            $rejectedCounts = $rejectedCounts->where('created_by', auth()->user()->id);

            $googlePosts->where('created_by', auth()->user()->id);
        }

        if (isset(request()->user) && request()->user != 'all') {
            $googlePosts->where('created_by', request()->user);

            $pendingCounts = $pendingCounts->where('created_by', request()->user);

            $rejectedCounts = $rejectedCounts->where('created_by', request()->user);

            $allCounts = $allCounts->where('created_by', request()->user);
        }

        $pendingCounts = $pendingCounts->count();

        $rejectedCounts = $rejectedCounts->count();

        $allCounts = $allCounts->count();

        $googlePosts = $googlePosts->paginate($request->paging);

        $googlePosts->getCollection()->transform(function ($item) {
            if ($item->backup_last_updated) {
                $item->last_updated_formatted = \Carbon\Carbon::parse($item->backup_last_updated)->diffForHumans([
                    'parts' => 2, // Show up to 2 units (e.g., "1 year 3 months")
                    'short' => false, // Use short units (e.g., "1y 3mo")
                    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE // Remove "ago"
                ]);
            } else {
                $item->last_updated_formatted = null;
            }
            return $item;
        });

        $users = User::where('status', 1)->get();

        return view('database-listing.publish-pending', compact('users', 'allCounts', 'googlePosts', 'pendingCounts', 'rejectedCounts'));
    }

    /**
     * Edit the Publish 
     *
     * @param int $id
     * @return void
     */
    public function editPublish($id)
    {
        $listing = Listing::find($id);

        $siteSetting = SiteSetting::first();

        if (!$url = $this->getSiteBaseUrl()) {
            session()->flash('message', 'Please complete your Site Setting Then Continue');

            return view('settings.error');
        }

        $response = Http::withoutVerifying()
            ->get($url . '/feeds/posts/default?alt=json');

        $publications  = WeightVSCourier::all();

        $categories = $response->json()['feed']['category'];
        return view('database-listing.publish-edit', compact('listing', 'siteSetting', 'categories', 'publications'));
    }

    public function fieldsAreChanged($productId)
    {
        $reference = BackupListing::where('product_id', $productId)
            ->where('last_updated', '<=', Carbon::now()->subYear())
            ->first();

        $changed = [];

        if (!$reference) return [$changed, false];

        if ($reference->title != request()->title) {
            $changed[] = 'title';
        }

        if ($reference->selling_price != request()->selling_price) {
            $changed[] = 'selling_price';
        }

        if ($reference->base_url != request()->base_url) {
            $changed[] = 'base_url';
        }

        return [$changed, true];
    }

    /**
     * Edit Post in DB
     */
    public function editInDB($id)
    {
        if (!$url = $this->getSiteBaseUrl()) {
            session()->flash('message', 'Please complete your Site Setting Then Continue');
            return view('settings.error');
        }

        $response = Http::withoutVerifying()->get($url . '/feeds/posts/default/' . $id . '?alt=json');

        $products = (object) ($response->json()['entry']);

        $images = [];
        $doc = new \DOMDocument();
        if (((array)($products->content))['$t']) {
            @$doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . ((array)($products->content))['$t']);
        }
        $td = $doc->getElementsByTagName('td');
        $a = $doc->getElementsByTagName('a');
        $div = $doc->getElementsByTagName('div');

        $price = explode('-', $td->item(1)->textContent ?? '');
        $selling = $price[0] ?? 0;
        $mrp = $price[1] ?? 0;
        $image = $doc->getElementsByTagName("img")?->item(0)?->getAttribute('src');
        $productId = explode('-', ((array)$products->id)['$t'])[2];
        $productTitle = ((array)$products->title)['$t'];
        $published = ((array)$products->published)['$t'];
        $updated = ((array)$products->updated)['$t'];

        $edition_author_lang = explode(',', $td->item(7)->textContent ?? '');
        // $author_name = $edition_author_lang[0];
        // $edition = $edition_author_lang[1] ?? '';
        // $lang = $edition_author_lang[2] ?? '';

        $bindingType = explode(',', $td->item(9)->textContent ?? '');
        // $binding = $bindingType[0] ?? '';
        // $condition = $bindingType[1] ?? '';

        $page_no = $td->item(11)->textContent ?? '';

        $instaUrl = "";
        for ($i = 0; $i < $a->length; $i++) {
            $item = trim($a->item($i)->textContent);
            if ($item == 'BUY AT INSTAMOJO') {
                $instaUrl = $a->item($i)->getAttribute('href');
            }
        }

        $sku = '';
        $publication = '';
        $isbn10 = '';
        $isbn13 = '';
        $publishyear = '';
        $weight = '';
        $age = '';
        $origin = '';
        $genre = '';
        $manufacturer = '';
        $importer = '';
        $packer = '';
        $lang = '';
        $edition = '';
        $author_name = '';
        $condition = '';
        $binding = '';

        if (count($edition_author_lang) > 1) {
            $author_name = $edition_author_lang[0];
            $edition = $edition_author_lang[1] ?? '';
            $lang = $edition_author_lang[2] ?? '';
        }

        if (count($bindingType) > 1) {
            $binding = $bindingType[0] ?? '';
            $condition = $bindingType[1] ?? '';
        }

        for ($i = 0; $i < $td->length; $i++) {
            if ($td->item($i)->getAttribute('itemprop') == 'sku') {
                $sku = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'color') {
                $publication = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'isbn10') {
                $isbn10 = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'isbn13') {
                $isbn13 = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'publishyear') {
                $publishyear = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'weight') {
                $weight = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'age') {
                $age = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'origin') {
                $origin = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'genre') {
                $genre = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'manufacturer') {
                $manufacturer = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'importer') {
                $importer = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'packer') {
                $packer = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'language') {
                $lang = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'edition') {
                $edition = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'author') {
                $author_name = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'condition') {
                $condition = trim($td->item($i)->textContent);
            }

            if ($td->item($i)->getAttribute('itemprop') == 'binding') {
                $binding = trim($td->item($i)->textContent);
            }
        }

        $desc = [];
        for ($i = 0; $i < $div->length; $i++) {
            if ($div->item($i)->getAttribute('class') == 'pbl box dtmoredetail dt_content') {
                // Access the individual DOMNode
                $node = $div->item($i);

                // Get the inner HTML content of the node (without the <div> tag)
                $innerHTML = '';
                foreach ($node->childNodes as $child) {
                    $innerHTML .= $node->ownerDocument->saveHTML($child);
                }

                // Add the inner HTML content to the $desc array
                $desc[] = $innerHTML;
            }
        }

        if ($doc->getElementsByTagName("img")->length > 1) {
            for ($i = 0; $i < $doc->getElementsByTagName("img")->length; $i++) {
                $imageElement = $doc->getElementsByTagName("img")->item($i);
                $images[] = $imageElement->getAttribute('src');
            }
        }

        $link = '';
        if (isset($products->link[4])) {
            $link = $products->link[4]['href'];
        } else {
            $link = $products->link[2]['href'];
        }

        if (strlen($publication) > 100) {
            $publication = explode(',', $publication)[0];
        }

        $productTitle = str_replace("'", "", trim($productTitle));

        $allInfo = [
            'product_id' => trim($productId),
            'title' => trim($productTitle),
            'description' => $desc[0] ?? '',
            'mrp' => (int) trim($mrp),
            'selling_price' => (int) trim($selling),
            'publisher' => trim($publication) ?? 'Exam360',
            'author_name' => trim($author_name),
            'edition' => trim($edition),
            'categories' => (collect($products->category ?? [])->pluck('term')->toArray()),
            'sku' => trim($sku),
            'language' => trim($lang),
            'no_of_pages' => trim($page_no),
            'binding_type' => trim($binding),
            'condition' => trim($condition),
            'insta_mojo_url' => trim($instaUrl),
            'images' => $image ?? '',
            'multipleImages' => $images,
            'url' => $link,
            'created_by' => auth()->user()->id,
            'status' => 0,
            'baseimg' => $image ?? '',
            'multiple_images' => $images ?? '',
            'isbn_10' => trim($isbn10),
            'isbn_13' => trim($isbn13),
            'publish_year' => trim($publishyear),
            'weight' => trim($weight),
            'reading_age' => trim($age),
            'country_origin' => trim($origin),
            'genre' => trim($genre),
            'manufacturer' => trim($manufacturer),
            'importer' => trim($importer),
            'packer' => trim($packer),
        ];

        $response = Http::withoutVerifying()
            ->get($url . '/feeds/posts/default?alt=json');

        $categories = $response->json()['feed']['category'];

        $siteSetting = SiteSetting::first();

        $labels = (collect($products->category ?? [])->pluck('term')->toArray());

        $listing = (object) ($allInfo);

        $publications  = WeightVSCourier::all();

        return view('database-listing.publish-edit', compact('categories', 'listing', 'labels', 'siteSetting', 'productId', 'productTitle', 'publications'));
    }

    /**
     * Edit the post in Database
     */
    public function publshInDB()
    {
        $productId = trim(request()->database);
        $backupListing = BackupListing::where('product_id', $productId)->first();

        $similarityPercentage = 0;
        $changePercentage = 0;

        if ($backupListing) {
            $oldStr = strip_tags(
                ($backupListing->title ?? '') .
                    ($backupListing->description ?? '') .
                    ($backupListing->edition ?? '') .
                    (string)$backupListing->mrp .
                    (string)$backupListing->selling_price
            );

            $newStr = strip_tags(
                trim(request()->title ?? '') .
                    (request()->description ?? '') .
                    trim(request()->edition ?? '') .
                    (string) trim(request()->mrp) .
                    (string) trim(request()->selling_price)
            );

            similar_text($oldStr, $newStr, $similarityPercentage);

            $changePercentage = 100 - $similarityPercentage;
        }

        $listing = $this->listingExist(request()->title, request()->description);

        if ($listing) {
            session()->flash('success', 'Listing already exists.');
            return redirect()->back();
        }

        $allInfo = [
            'product_id' => trim(request()->database),
            'title' => trim(request()->title),
            'description' => request()->description ?? '',
            'mrp' => (int) trim(request()->mrp),
            'selling_price' => (int) trim(request()->selling_price),
            'publisher' => trim(request()->publication) ?? 'Exam360',
            'author_name' => trim(request()->author_name),
            'edition' => trim(request()->edition),
            'categories' => request()->label,
            'sku' => trim(request()->sku),
            'language' => trim(request()->language),
            'no_of_pages' => trim(request()->pages),
            'binding_type' => trim(request()->binding),
            'condition' => trim(request()->condition),
            'insta_mojo_url' => trim(request()->url),
            'images' => json_encode(request()->images) ?? "",
            'multiple_images' => request()->multipleImages ? json_encode(request()->multipleImages) : null,
            'url' => request()->product_url,
            'created_by' => auth()->user()->id,
            'status' => 0,
            'isbn_10' => trim(request()->isbn_10),
            'isbn_13' => trim(request()->isbn_13),
            'publish_year' => trim(request()->publish_year),
            'weight' => trim(request()->weight),
            'reading_age' => trim(request()->reading_age),
            'country_origin' => trim(request()->country_origin),
            'genre' => trim(request()->genre),
            'manufacturer' => trim(request()->manufacturer),
            'importer' => trim(request()->importer),
            'packer' => trim(request()->packer),
            'similarity_percentage' => round($similarityPercentage, 2),
            'change_percentage' => round($changePercentage, 2),
        ];

        $listing = Listing::create($allInfo);

        if (request()->modify_id) {
            \App\Models\ListingModifyRequest::where('id', request()->modify_id)->update([
                'status' => 'Completed',
                'updated_by' => auth()->id()
            ]);
        }

        if (!isset(request()->duplicate)) {
            $featureImage = json_decode($listing->images);
            $data = [
                'image' => $featureImage ? $featureImage[0] : null,
                'title' => $listing->title,
                'created_by' => auth()->user()->id,
                'approved_by' => null,
                'approved_at' => null,
                'status' => 0,
                'status_listing' => !request()->price_issue ? 'Edited' : 'Price Issue',
                'listings_id' =>  $listing->id
            ];

            UserListingInfo::create($data);

            if (!request()->price_issue) {
                $this->updateTheCount('Edited', 'create_count');
            } else {
                $this->updateTheCount('Price Issue', 'create_count');
            }

            session()->flash('success', 'Pending for Approval');
        } else {
            session()->flash('success', 'Listing created successfully');
        }

        return redirect()->route('publish.pending', ['startIndex' => '1', 'category' => 'Product']);
    }

    /**
     * List the Articles
     *
     * @return void
     */
    public function articles()
    {
        $articles = BackupListing::where('categories', 'NOT LIKE', '%Product%')
            ->paginate(150);

        return view('listing.articles', compact('articles'));
    }

    /**
     * Update or Create Count
     *
     * @param string $status
     * @return void
     */
    public function updateTheCount($status, $column)
    {
        // Get the current date
        $currentDate = Carbon::now()->toDateString(); // This will give you 'YYYY-MM-DD' format

        // Check if a record exists for the current date and user
        $userListingCount = UserListingCount::where('user_id', auth()->user()->id)
            ->where('status', $status)
            ->whereDate('created_at', $currentDate)
            ->first();

        if ($userListingCount) {
            // If record exists, increment the approved_count
            $userListingCount->increment($column);
            $userListingCount->status = $status; // Update status if needed
            $userListingCount->save();
        } else {
            // If no record exists, create a new record
            UserListingCount::create([
                'user_id' => auth()->user()->id,
                $column => 1,
                'status' => $status,
            ]);
        }
    }

    public function listingExist($title, $description)
    {
        $listing = Listing::where('title', $title)->first();

        $backupListing = BackupListing::where('title', $title)->first();

        if (!$listing || !$backupListing) return false;

        return true;
    }

    public static function cleanText($text)
    {
        $text = strtolower(strip_tags($text));
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    public static function textSimilarity($text1, $text2)
    {
        $words1 = explode(' ', self::cleanText($text1));
        $words2 = explode(' ', self::cleanText($text2));
        $words1 = array_unique($words1);
        $words2 = array_unique($words2);
        $common = array_intersect($words1, $words2);
        $similarity = (count($common) / max(count($words1), count($words2))) * 100;
        return round($similarity, 2);
    }

    public static function getNgrams($text, $n = 3)
    {
        $words = explode(' ', self::cleanText($text));
        $ngrams = [];
        for ($i = 0; $i < count($words) - $n + 1; $i++) {
            $ngrams[] = implode(' ', array_slice($words, $i, $n));
        }
        return $ngrams;
    }

    public static function ngramSimilarity($text1, $text2, $n = 3)
    {
        $ngrams1 = self::getNgrams($text1, $n);
        $ngrams2 = self::getNgrams($text2, $n);
        $common = array_intersect($ngrams1, $ngrams2);
        $similarity = (count($common) / max(count($ngrams1), count($ngrams2))) * 100;
        return round($similarity, 2);
    }

    public function reviewPriceIssue()
    {
        $perPage = request()->paging ?? 10;
        $listings = BackupListing::whereRaw('(mrp - selling_price) BETWEEN 1 AND 10')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('database-listing.price-issue', compact('listings'));
    }

    public function getPublisher()
    {
        $perPage = request()->paging ?? 10;
        $listings = BackupListing::whereRaw('(mrp - selling_price) BETWEEN 1 AND 5')
            ->select('publisher')
            ->groupBy('publisher')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('database-listing.publisher', compact('listings'));
    }

    public function getSpecificPublisher($publisher)
    {
        $perPage = request()->paging ?? 10;
        $listings = BackupListing::whereRaw('(mrp - selling_price) BETWEEN 1 AND 5')
            ->where('publisher', $publisher)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('database-listing.specific-publisher', compact('listings'));
    }

    public function export()
    {
        $listings = BackupListing::whereRaw('(mrp - selling_price) BETWEEN 1 AND 5')
            ->select('publisher')
            ->groupBy('publisher')
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.publishers', [
            'listings' => $listings
        ]);

        return $pdf->download('publishers.pdf');
    }
}
