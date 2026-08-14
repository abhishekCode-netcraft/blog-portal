<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class WatermarkController extends Controller
{
    const CANVAS_WIDTH = 555;
    const CANVAS_HEIGHT = 555;
    const COLOR_BACKGROUND = '#ffffff';

    /**
     * Show the create view.
     */
    public function create()
    {
        return view('watermark.create');
    }

    /**
     * Store watermark
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:jpeg,png,jpg',
            'image_url' => 'nullable|url',
        ]);

        if (!$request->hasFile('file') && !$request->filled('image_url')) {
            return response()->json(['error' => 'Please provide an image file or image URL.'], 422);
        }

        $siteSettings = SiteSetting::first();
        $files = [];
        $lastNumber = 0;

        foreach (File::glob(storage_path('app/public/uploads') . '/*') as $key => $path) {
            $filepath = explode('/', $path);
            $name = (int) explode('.', end($filepath))[0];

            if ($name > $lastNumber) {
                $lastNumber = $name;
            }

            $files[$key]['name'] = end($filepath);
            $files[$key]['number'] = $name;
        }

        $countIncrease = $lastNumber + 1;
        $filename = $countIncrease . "." . request()->format;
        $localPath = storage_path('app/public/uploads/' . $filename);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file->storeAs('public/uploads', $filename);
        } else if ($request->filled('image_url')) {
            $imageContents = file_get_contents($request->image_url);
            file_put_contents($localPath, $imageContents);
        }

        $widthSizeImage = 390;
        $heightSizeImage = 520;
        if (request()->size == 'basic') {
            $widthSizeImage = 390;
            $heightSizeImage = 520;
        } else if (request()->size == 'fixed') {
            $widthSizeImage = 320;
            $heightSizeImage = 450;
        }

        // Resize and create base image
        $background = (new ImageManager())->canvas(self::CANVAS_WIDTH, self::CANVAS_HEIGHT, self::COLOR_BACKGROUND);
        $background->insert(Image::make($localPath)->resize($widthSizeImage, $heightSizeImage), 'center');

        // Watermark logic
        if ($request->with_watermark) {
            $fontSize = min($background->width(), $background->height()) / 20;
            $centerX = $background->width() / 2;
            $centerY = $background->height() / 2;
            $watermarkText = $siteSettings->watermark_text ?? 'Exam 360';
            $numPoints = max(strlen($watermarkText), 1);
            $radius = min($background->width(), $background->height()) / 2;

            for ($i = 1; $i < $numPoints; $i++) {
                $angle = $i * (360 / $numPoints) + 55;
                $x = $centerX + $radius * cos(deg2rad($angle));
                $y = $centerY + $radius * sin(deg2rad($angle));

                $background->text($watermarkText, $x, $y, function ($font) use ($fontSize) {
                    $font->file(public_path('arial.ttf'));
                    $font->color([128, 128, 128, 0.5]);
                    $font->size($fontSize);
                    $font->angle(45);
                    $font->align('center');
                    $font->valign('center');
                });
            }

            $background->text($watermarkText, $centerX, $centerY, function ($font) use ($fontSize) {
                $font->file(public_path('arial.ttf'));
                $font->color([128, 128, 128, 0.5]);
                $font->size($fontSize);
                $font->angle(45);
                $font->align('center');
                $font->valign('center');
            });
        }

        $background->save($localPath);

        // Handle file or URL
        if ($request->hasFile('file')) {
            $imageContent = Storage::get("public/uploads/{$filename}");
        }

        session()->put('watermarkFileUrl', $filename);

        if ($request->type == 'json') {
            return response()->json([
                'url' => url("storage/uploads/{$filename}"),
                'filename' => $filename,
            ]);
        } else {
            return Response::make($imageContent, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        }
    }
}
