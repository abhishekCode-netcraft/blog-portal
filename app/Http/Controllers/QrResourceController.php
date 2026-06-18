<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QR;

class QrResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $qrs = QrCode::latest()->paginate(10);

        return view('qr.index', compact('qrs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('qr.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'title'         => 'required|max:255',
                'redirect_url'  => 'required|url',
                'url_type'      => 'required|in:auto,custom',
                'custom_slug'   => 'nullable|string|max:100|alpha_dash|unique:qr_codes,slug',
            ]);

            if ($request->url_type === 'custom') {
                $slug = $request->custom_slug;
            } else {
                do {
                    $slug = Str::random(8);
                } while (QrCode::where('slug', $slug)->exists());
            }

            $qrUrl = route('qr-resource.redirect', $slug);

            /*
            * Generate QR PNG
            */
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrUrl)
                ->size(300)
                ->build();
            // $qrImage = QR::format('png')
            //     ->size(250)
            //     ->generate($qrUrl);

            /*
            * Save image
            */
            $fileName = 'qrcodes/' . time() . '.png';

            Storage::disk('public')->put($fileName, $result->getString());

            /*
            * Save DB
            */
            $qr = QrCode::create([
                'title'         => $request->title,
                'redirect_url'  => $request->redirect_url,
                'url_type'      => $request->url_type,
                'slug'          => $slug,
                'qr_image'      => $fileName,
            ]);

            session()->put('qr_download', $qrUrl); 
            
            $qrCode = QR::size(250)->generate($qrUrl); 
            
            return view('qr.create', compact('qrCode', 'qr', 'qrUrl'));
            // return redirect()
            //     ->route('qr-resource.create')
            //     ->with('success', 'QR Code created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadSingleQR()
    {
        $url = session('qr_download');

        // abort_if(!$url, 404);

        // $png = QrCode::format('jpg')
        //     ->size(300)
        //     ->generate($url);

        // return response($png)
        //     ->header('Content-Type', 'image/jpg')
        //     ->header(
        //         'Content-Disposition',
        //         'attachment; filename="qrcode.jpg"'
        //     );

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->size(300)
            ->build();

        return response($result->getString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qrcode.png"');
    }

    public function download($id)
    {
        $qr = QrCode::findOrFail($id);

        $url = route('qr-resource.redirect', $qr->slug);

        // $png = QR::format('png')
        //     ->size(300)
        //     ->generate($url);

        // return response($png)
        //     ->header('Content-Type', 'image/png')
        //     ->header(
        //         'Content-Disposition',
        //         'attachment; filename="' . $qr->slug . '.png"'
        //     );

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->size(300)
            ->build();

        return response($result->getString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qrcode.png"');
    }

    public function redirect($slug)
    {
        $qr = QrCode::where('slug', $slug)->firstOrFail();

        $qr->increment('scan_count');

        return redirect()->away($qr->redirect_url);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        dd('QR Resource Controller Show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $qr = QrCode::findOrFail($id);

        return view('qr.edit', compact('qr'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $qr = QrCode::findOrFail($id);

        $request->validate([
            'redirect_url' => 'required|url',
        ]);

        $qr->update([
            'redirect_url' => $request->redirect_url,
        ]);

        return redirect()
            ->route('qr-resource.index')
            ->with('success', 'Redirect URL updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $qr = QrCode::findOrFail($id);

        $qr->delete();

        return redirect()
            ->route('qr-resource.index')
            ->with('success', 'QR code deleted successfully.');
    }
}
