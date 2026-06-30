<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageMakerController extends Controller
{

    /**
     * Constructor
     *
     * @param GoogleService $googleService
     */
    public function __construct()
    {
        $this->middleware('role_or_permission:Image Creation -> Single Image Maker', ['only' => ['singleImage']]);
        $this->middleware('role_or_permission:Image Creation -> Combo Image Maker', ['only' => ['comboImage']]);
        $this->middleware('role_or_permission:Image Creation -> Gallery ( DB )', ['only' => ['imageGallery']]);
    }

    /**
     * Invoke Function
     *
     * @param string $file
     * @return void
     */
    public function __invoke($file)
    {
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);

        $basePath = storage_path('app/public/uploads/');

        $filePath1 = $basePath . $file;
        $filePath2 = $basePath . 'posts/' . $file;
        $filePath3 = storage_path('app/public/') . 'complaints/replies/' . $file;
        $filePath4 = storage_path('app/public/') . 'complaints/files/' . $file;
        $filePath5 = storage_path('app/public/') . 'review-screenshots/' . $file;

        if (file_exists($filePath1)) {
            return response()->file($filePath1);
        }

        if (file_exists($filePath2)) {
            return response()->file($filePath2);
        }

        if (file_exists($filePath3)) {
            return response()->file($filePath3);
        }

        if (file_exists($filePath4)) {
            return response()->file($filePath4);
        }

        if (file_exists($filePath5)) {
            return response()->file($filePath5);
        }

        abort(404, 'File not found.');
    }

    /**
     * Show the create view.
     */
    public function singleImage()
    {
        $images = [];
        foreach (File::glob(public_path('images') . '/*') as $path) {
            $filepath = explode('/', $path);
            foreach ($filepath as $name) {
                if (strpos($name, '.jpg') !== false || strpos($name, '.png') !== false) {
                    $images[] = $name;
                }
            }
        }

        return view('image-creation.single', compact('images'));
    }

    /**
     * Show the create view.
     */
    public function comboImage()
    {
        $images = [];
        foreach (File::glob(storage_path('app/public/uploads') . '/*') as $path) {
            $filepath = explode('/', $path);
            foreach ($filepath as $name) {
                if (str_contains($name, '.jpg')) {
                    $images[] = $name;
                }
            }
        }

        return view('image-creation.combo', compact('images'));
    }

    /**
     * Image Gallery
     *
     * @return void
     */
    public function imageGallery()
    {
        $files = [];
        foreach (File::glob(storage_path('app/public/uploads') . '/*') as $key => $path) {
            $filepath = explode('/', $path);

            foreach ($filepath as $name) {
                if (strpos($name, '.jpg') !== false || strpos($name, '.png') !== false) {
                    $files[$key]['name'] = $name;

                    $filePath = @exif_read_data(storage_path('/app/public/uploads/' . $name))['FileDateTime'];
                    $fileSize = @exif_read_data(storage_path('/app/public/uploads/' . $name))['FileSize'];

                    $dateTime = Carbon::createFromTimestamp($filePath);
                    $files[$key]['datetime'] = $dateTime->toDateTimeString();
                    $files[$key]['size'] = $fileSize;
                }
            }
        }

        // Custom sorting function for descending order
        usort($files, function ($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });

        $files = $this->paginate($files, request()->count, request()->page);

        return view('image-creation.image-gallery', compact('files'));
    }

    /**
     * Paginate  All Queries
     *
     * @param array $items
     * @param int $perPage
     * @param int $page
     * @param array $options
     * @return void
     */
    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        $paginated = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);

        // Append existing GET parameters to pagination links
        $paginated->appends(request()->query());

        // Set the current URL as the base path for pagination
        $paginated->setPath(request()->url());

        return $paginated;
    }

    /**
     * Refresh the URLs
     */
    public function refreshURL()
    {
        if (request()->session)
            return session()->get(request()->session);
    }

    /**
     * Delete Images
     *
     * @return void
     */
    public function deleteImage()
    {
        if (request()->ajax()) {
            foreach (request()->formData[1] as $image) {
                if (Storage::exists('/public/uploads/' . $image)) {
                    Storage::delete('/public/uploads/' . $image);
                }
            }

            return true;
        }

        if (Storage::exists('/public/uploads/' . request()->name)) {
            Storage::delete('/public/uploads/' . request()->name);
        }

        session()->flash('success', 'Image Delete Successfully');

        return redirect()->back();
    }
}
