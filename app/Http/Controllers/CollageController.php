<?php

namespace App\Http\Controllers;

use App\Generators\CustomThreeImage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;
use App\Generators\FourImage;
use App\Generators\OneImage as GeneratorsOneImage;
use App\Generators\TwoImage;
use Tzsk\Collage\MakeCollage;

class CollageController extends Controller
{
    const CANVAS_WIDTH = 555;
    const CANVAS_HEIGHT = 555;
    const COLOR_BACKGROUND = '#ffffff';


    /**
     * Show the create view.
     */
    public function create()
    {
        return view('collage.create');
    }

    public function store()
    {
        $validated = $this->validate(request(), [
            'file'              => 'required|array',
            'file.*'            => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'title.required_if' => 'The title field is required when adding a watermark.',
        ]);

        $processedImages = [];

        foreach ($validated['file'] as $file) {
            $processedImages[] = Image::make($file);
        }

        $image = (new MakeCollage())->with([
            1 => GeneratorsOneImage::class,
            2 => TwoImage::class,
            3 => CustomThreeImage::class,
            4 => FourImage::class,
        ])
            ->make(self::CANVAS_WIDTH, self::CANVAS_HEIGHT)->padding(80)
            ->background(self::COLOR_BACKGROUND)
            ->from($processedImages, function ($alignment) use ($processedImages) {
                $imageCount = count($processedImages);
                if ($imageCount == 2) {
                    $alignment->vertical();
                } else if ($imageCount == 3) {
                    $alignment->twoTopOneBottom();
                } else if ($imageCount == 4) {
                    $alignment->grid();
                }
            });

        $files = [];
        $lastNumber = 0;

        foreach (File::glob(storage_path('app/public/uploads') . '/*') as $key => $path) {
            $filepath = explode('/', $path);
            $name = (int) explode('.', $filepath[8])[0];

            if ($name > $lastNumber) {
                $lastNumber = $name;
            }

            $files[$key]['name'] = $filepath[8];
            $files[$key]['number'] = $name;
        }

        $countIncrease = $lastNumber + 1;

        $filename = $countIncrease . "." . request()->format;

        $path = 'storage/uploads/' . $filename;

        $image->save($path);

        $imageContent = file_get_contents($path);

        if (request()->input('is_with_watermark')) {
            $siteSettings = SiteSetting::first();

            $imageContent = $this->addWaterMark($image, $siteSettings->watermark_text ?? 'shop.Exam360.in');
        }

        Session::put('fileurl', $filename);

        return Response::make($imageContent, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }

    public function addWaterMark($image, $title)
    {
        $fontSize = min($image->width(), $image->height()) / 25;

        $centerX = $image->width() / 2;
        $centerY = $image->height() / 2;

        $watermarkLength = strlen($title);
        $numPoints = max($watermarkLength, 1);

        $radius = min($image->width(), $image->height()) / 2;

        for ($i = 1; $i < $numPoints; $i++) {
            $angle = $i * (360 / $numPoints) + 55;

            $x = $centerX + $radius * cos(deg2rad($angle));
            $y = $centerY + $radius * sin(deg2rad($angle));

            $image->text($title, $x, $y, function ($font) use ($fontSize) {
                $font->file(public_path('arial.ttf'));
                $font->color([128, 128, 128, 0.5]);
                $font->size($fontSize);
                $font->angle(45);
                $font->align('center');
                $font->valign('center');
            });
        }

        $image->text($title, $centerX, $centerY, function ($font) use ($fontSize) {
            $font->file(public_path('arial.ttf'));
            $font->color([128, 128, 128, 0.5]);
            $font->size($fontSize);
            $font->angle(45);
            $font->align('center');
            $font->valign('center');
        });

        $image->save();

        return $image->encoded;
    }
}
