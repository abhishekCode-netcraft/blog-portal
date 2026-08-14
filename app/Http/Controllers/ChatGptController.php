<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPriceFileJob;
use App\Models\GeneratePriceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ChatGptService;

class ChatGptController extends Controller
{
    protected $chatGptService;

    public function __construct(ChatGptService $chatGptService)
    {
        $this->chatGptService = $chatGptService;
        $this->middleware('role_or_permission:Product Listing > AI Chat Bots', ['only' => ['openAi']]);
    }

    public function openAi()
    {
        return view('chatGpt.new_request');
    }

    public function responseAiDescription(Request $request)
    {
        try {
            $data = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])->post(env('OPENAI_API_URL'), [
                "model" => "gpt-3.5-turbo",
                'messages' => [
                    [
                        "role" => "user",
                        "content" => $request->product_info
                    ],
                ],
                'temperature' => 0.5,
                "max_tokens" => 500,
                "top_p" => 1.0,
                "frequency_penalty" => 0.52,
                "presence_penalty" => 0.5,
                "stop" => ["11."],
            ])->json();

            if (isset($data['error'])) {
                session()->flash('error', 'Something went Wrong!!');
                return redirect()->back();
            } else {
                $user_request = $request->product_info;
                $assistent_response = $data['choices'][0]['message'];

                return view('chatGpt.new_request', compact('user_request', 'assistent_response'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Something went Wrong!!');

            return redirect()->back();
        }
    }

    public function responseAidescriptionfetcher(Request $request)
    {
        try {
            $result = $this->chatGptService->getMessage($request->product_info);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'status' => true,
                'content' => $result['content'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadFile()
    {
        $files = GeneratePriceFile::latest()->paginate(10);

        return view('generate-price.index', compact('files'));
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'price_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            $file = $request->file('price_file');
            $path = $file->store('price-files', 'public');

            $priceFile = GeneratePriceFile::create([
                'original_name' => $file->getClientOriginalName(),
                'file_name'     => basename($path),
                'file_path'     => $path,
                'file_type'     => $file->getClientMimeType(),
                'file_size'     => $file->getSize(),
                'status'        => 'Pending',
            ]);

            ProcessPriceFileJob::dispatch($priceFile);

            // return response()->json([
            //     'success' => true,
            //     'id' => $priceFile->id,
            // ]);

            return back()->with('success', 'File uploaded successfully. Processing has started.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id)
    {
        $file = GeneratePriceFile::findOrFail($id);

        return response()->json([
            'status' => $file->status,
            'progress' => $file->progress,
            'download' => $file->processed_file
                ? asset('storage/' . $file->processed_file)
                : null,
        ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  \App\Models\GeneratePriceFile $generatePriceFile
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $file = GeneratePriceFile::findOrFail($id);
        $file->delete();

        return redirect()
            ->route('upload.price.file')
            ->with('success', 'Log deleted successfully!');
    }
}
