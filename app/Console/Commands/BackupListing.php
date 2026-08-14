<?php

namespace App\Console\Commands;

use App\Exports\BackupListingsExport;
use App\Mail\BackupMail;
use App\Models\BackupEmail;
use App\Models\BackupListing as ModelsBackupListing;
use App\Models\BackupListingImage;
use App\Models\BackupLogs;
use App\Models\SiteSetting;
use App\Services\GoogleService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Carbon\Carbon;
use Maatwebsite\Excel\Excel as ExcelFormat;

class BackupListing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:listing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will insert the blogger data to Database and export file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $batchId = str_pad(0000001, 7, '0', STR_PAD_LEFT);
            if ($lastLog = BackupLogs::latest()->first()) {
                $batchId = str_pad(++$lastLog->batch_id, 7, '0', STR_PAD_LEFT);
            }

            $started = now();
            $this->getAllProducts();
            $completed = now();

            $listingData = app('App\Http\Controllers\BackupListingsController');
            $currentTimeInSeconds = now()->timestamp;
            $currentDateTimeFormat = now()->format('Y-m-d H:i:s');
            $currentDateFormat = now()->format('Y-m-d');
            $fileName = 'report-' . $currentTimeInSeconds . '.xlsx';
            $sqlfileName = 'export-file' . $currentTimeInSeconds . '.sql';

            $this->generateXLSFile($currentTimeInSeconds, $listingData);

            $this->generateGoogleMerchantCenterFile($currentTimeInSeconds, $listingData);

            $this->generateFacebookPixelFile($currentTimeInSeconds, $listingData);

            $this->generateSQLFile($currentTimeInSeconds, $listingData);

            $emailTo = $this->createZipAndMail($currentTimeInSeconds, $fileName, $sqlfileName);

            $this->deleteTenDaysAboveFiles();
            
            $this->backupToDropBox($currentDateFormat, $fileName, $sqlfileName);
            
            $this->deleteThreeMonthsOldFiles();

            BackupLogs::create([
                'batch_id' => $batchId,
                'started' => $started,
                'completed' => $completed,
                'export_file' => $currentDateTimeFormat,
                'merchant_file' => $currentDateTimeFormat,
                'facebook_file' => $currentDateTimeFormat,
                'email_to' => implode(",", $emailTo),
                'sql_file' => $currentDateTimeFormat,
            ]);
        } catch (\Exception $e) {
            BackupLogs::create([
                'batch_id' => $batchId,
                'started' => now(),
                'completed' => now(),
                'export_file' => now(),
                'merchant_file' => now(),
                'facebook_file' => now(),
                'email_to' => '',
                'sql_file' => now(),
                'error' => $e->getMessage()
            ]);

            Log::channel('backup_activity_log')->info('Facing some issues');

            Log::info($e);
        }
    }

    /**
     * Get all Products
     *
     * @param GoogleService $googleService
     * @return array
     */
    public function getAllProducts()
    {
        $url = $this->getSiteBaseUrl();
        $response = Http::withOptions([
            'force_ip_resolve' => 'v4',
        ])->get('https://shop.exam360.in/feeds/posts/default?alt=json');

        // $response = Http::get($url . 'feeds/posts/default?alt=json');
        $totalProducts =  $response->json()['feed']['openSearch$totalResults']['$t'];

        $paginate = 1;
        for ($j = 0; $j < (int) ($totalProducts / 150) + 1; $j++) {
            // for ($i = 0; $i <= 2; $i++) {
            $allProducts = app('App\Services\GoogleService')->backupToDatabse($paginate);

            foreach ($allProducts['paginator'] as $key => $products) {
                $updatedDate = ((array)($products->updated))['$t'];
                $date = \Carbon\Carbon::parse($updatedDate)->toDateString();
                $images = [];
                $doc = new \DOMDocument();
                if (((array)($products->content))['$t']) {
                    @$doc->loadHTML(((array)($products->content))['$t']);
                }
                $td = $doc->getElementsByTagName('td');
                $a = $doc->getElementsByTagName('a');
                $div = $doc->getElementsByTagName('div');

                $price = explode('-', $td->item(1)->textContent ?? '');
                $selling = $price[0] ?? 0;
                $mrp = $price[1] ?? 0;
                $image = $doc->getElementsByTagName("img")?->item(0)?->getAttribute('src');
                $productId = explode('-', ((array)$allProducts['paginator'][$key]->id)['$t'])[2];
                $productTitle = ((array)$allProducts['paginator'][$key]->title)['$t'];
                $published = ((array)$allProducts['paginator'][$key]->published)['$t'];
                $updated = ((array)$allProducts['paginator'][$key]->updated)['$t'];

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

                $sku = null;
                $publication = null;
                $isbn10 = null;
                $isbn13 = null;
                $publishyear = null;
                $weight = null;
                $age = null;
                $origin = null;
                $genre = null;
                $manufacturer = null;
                $importer = null;
                $packer = null;
                $lang = null;
                $edition = null;
                $author_name = null;
                $binding = null;
                $condition = null;

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
                    $itemprop = $td->item($i)->getAttribute('itemprop');
                    $value = trim($td->item($i)->textContent);

                    switch ($itemprop) {
                        case 'sku':
                            $sku = $value;
                            break;
                        case 'color':
                            $publication = $value;
                            break;
                        case 'isbn10':
                            $isbn10 = $value;
                            break;
                        case 'isbn13':
                            $isbn13 = $value;
                            break;
                        case 'publishyear':
                            $publishyear = $value;
                            break;
                        case 'weight':
                            $weight = $value;
                            break;
                        case 'age':
                            $age = $value;
                            break;
                        case 'origin':
                            $origin = $value;
                            break;
                        case 'genre':
                            $genre = $value;
                            break;
                        case 'manufacturer':
                            $manufacturer = $value;
                            break;
                        case 'importer':
                            $importer = $value;
                            break;
                        case 'packer':
                            $packer = $value;
                            break;
                        case 'language':
                            $lang = $value;
                            break;
                        case 'edition':
                            $edition = $value;
                            break;
                        case 'author':
                            $author_name = $value;
                            break;
                        case 'condition':
                            $condition = $value;
                            break;
                        case 'binding':
                            $binding = $value;
                            break;
                    }
                }

                $desc = [];
                for ($i = 0; $i < $div->length; $i++) {
                    if ($div->item($i)->getAttribute('class') == 'pbl box dtmoredetail dt_content') {
                        $desc[] = str_replace("'", "", trim($div->item($i)->textContent));
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
                    $link = $products->link[4]->href;
                } else {
                    $link = $products->link[2]->href;
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
                    'categories' => json_encode(collect($products->category ?? [])->pluck('term')->toArray()),
                    'sku' => trim($sku),
                    'language' => trim($lang),
                    'no_of_pages' => trim($page_no),
                    'binding_type' => trim($binding),
                    'condition' => $condition,
                    'insta_mojo_url' => trim($instaUrl),
                    'base_url' => $image ?? '',
                    'multiple' => $images,
                    'url' => $link,
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
                    'last_updated' => $date
                ];


                if ($product = ModelsBackupListing::where("product_id", $productId)->first()) {
                    $product->update($allInfo);

                    if ($additionalImage = BackupListingImage::find($product->id)) {
                        foreach ($allInfo['multiple'] as $imageUrl) {
                            $additionalImage->update([
                                'image_url' => $imageUrl,
                            ]);
                        }
                    }
                } else {
                    $listing = ModelsBackupListing::create($allInfo);

                    foreach ($allInfo['multiple'] as $imageUrl) {
                        BackupListingImage::create([
                            'listing_id' => $listing->id,
                            'image_url' => $imageUrl,
                        ]);
                    }
                }
            }

            $paginate = $paginate + 150;
        }
    }

    /**
     * Get Site Base URL
     *
     * @return void
     */
    public function getSiteBaseUrl()
    {
        $siteSetting = SiteSetting::first();

        if ($siteSetting) return $siteSetting->url;
    }

    /**
     * Backup to Dropbox
     *
     * @return void
     */
    public function backupToDropBox($folderName, $firstFile, $secondFile)
    {
        $data = [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => env('DROPBOX_REFRESH_TOKEN'),
                'client_id' => env('DROPBOX_APP_KEY'),
                'client_secret' => env('DROPBOX_APP_SECRET')
            ]
        ];

        $client = new Client();

        $response = $client->post('https://api.dropbox.com/oauth2/token', $data);

        $tokenData = json_decode($response->getBody(), true);

        $token = $tokenData['access_token']; // should be replaced with the OAuth 2 access token

        $headers = [
            'Authorization' =>  'Bearer ' . $token,
            'Content-Type' =>  'application/octet-stream',
        ];

        $headers['Dropbox-API-Arg'] = json_encode([
            "path" => "/Backup - " . $folderName . '/' . $firstFile,
            "mode" => "add",
            "autorename" => true,
            "mute" => false
        ]);

        $client->post(env('DROPBOX_URL'), [
            'headers' => $headers,
            'body' => fopen(storage_path('app/public/' . $firstFile), 'r')
        ]);

        $headers['Dropbox-API-Arg'] = json_encode([
            "path" => "/Backup - " . $folderName . '/' . $secondFile,
            "mode" => "add",
            "autorename" => true,
            "mute" => false
        ]);

        $reponse = $client->post(env('DROPBOX_URL'), [
            'headers' => $headers,
            'body' => fopen(storage_path('app/public/' . $secondFile), 'r')
        ]);
    }

    /**
     * Generate Google Merchant File
     */
    public function generateGoogleMerchantCenterFile($currentTimeInSeconds, $listingData)
    {
        $googleMerchantfileName = 'merchant-file' . $currentTimeInSeconds . '.xlsx';
        $singleGoogleMerchantfileName = 'merchant-file.xlsx';

        Excel::store(new BackupListingsExport($listingData->getMerchantExportFile()), "/public/" . $googleMerchantfileName);
        Excel::store(new BackupListingsExport($listingData->getMerchantExportFile()), "/public/" . $singleGoogleMerchantfileName);
    }

    /**
     * Generate Facebook Pixel File
     */
    public function generateFacebookPixelFile($currentTimeInSeconds, $listingData)
    {
        $facebookPixelfileName = 'facebook-file' . $currentTimeInSeconds . '.csv';
        $singleFacebookPixelfileName = 'facebook-file.csv';

        Excel::store(new BackupListingsExport($listingData->getFacebookExportFile()), "/public/" . $facebookPixelfileName);
        Excel::store(new BackupListingsExport($listingData->getFacebookExportFile()), "/public/" . $singleFacebookPixelfileName);
    }

    /**
     * Generate SQL file
     */
    public function generateSQLFile($currentTimeInSeconds, $listingData)
    {
        $sqlfileName = 'export-file' . $currentTimeInSeconds . '.sql';
        Storage::disk('public')->put($sqlfileName, $listingData->exportSQL()[1]);
    }

    /**
     * Generate XLS file
     */
    public function generateXLSFile($currentTimeInSeconds, $listingData)
    {
        // $fileName = 'report-' . $currentTimeInSeconds . '.xlsx';
        // Excel::store(new BackupListingsExport($listingData->exportData()), "/public/" . $fileName);

        $fileName = 'report-' . $currentTimeInSeconds . '.csv';
        Excel::store(
            new BackupListingsExport($listingData->exportData()),
            'public/' . $fileName,
            null,
            ExcelFormat::CSV
        );
    }

    /**
     * Generate ZIP file and Mail
     */
    public function createZipAndMail($currentTimeInSeconds, $fileName, $sqlfileName)
    {
        $zip = new ZipArchive;
        $zipFileName = 'backup-files' . $currentTimeInSeconds . '.zip';
        if ($zip->open(storage_path("app/public/" . $zipFileName), ZipArchive::CREATE) === true) {
            $zip->addFile(storage_path("app/public/" . $fileName), basename(storage_path("app/public/" . $fileName)));
            $zip->addFile(storage_path("app/public/" . $sqlfileName), basename(storage_path("app/public/" . $sqlfileName)));
        }

        // Close the zip archive
        $zip->close();

        $allEmails = BackupEmail::all();
        $emailTo = [];
        foreach ($allEmails as $email) {
            Mail::to($email->email)->send(new BackupMail(storage_path("app/public/" . $zipFileName)));
            $emailTo[] = $email->email;
        }

        return $emailTo;
    }

    /**
     * Delete the files above 10 Days 
     *
     * @return void
     */
    public function deleteTenDaysAboveFiles()
    {
        // Get all files in the directory
        $files = Storage::files('public');

        // Current time
        $now = Carbon::now();

        foreach ($files as $file) {
            // Get the file's last modified time
            $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));

            // Check if the file is older than 10 days
            if ($now->diffInDays($lastModified) > 10) {
                // Delete the file
                Storage::delete($file);
            }
        }
    }

    /**
     * Delete the files above 3 Months 
     *
     * @return void
     */
    public function deleteThreeMonthsOldFiles()
    {
        $files = Storage::files('public/uploads');

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            $fileDate = Carbon::createFromTimestamp($lastModified);

            if ($fileDate->lt(Carbon::now()->subMonths(3))) {
                Storage::delete($file);
            }
        }
    }
}
