<?php

namespace App\Jobs;

use App\Models\GeneratePriceFile;
use App\Services\ChatGptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessPriceFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public GeneratePriceFile $priceFile)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ChatGptService $chatGptService)
    {
        $this->priceFile->update([
            'status' => 'Processing'
        ]);

        try {
            $path = storage_path('app/public/' . $this->priceFile->file_path);

            $data = Excel::toArray([], $path);

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('F1', 'Current Price');
            $sheet->setCellValue('G1', 'API Error');

            $totalRows = count($data[0]) - 1;
            $processed = 0;

            foreach ($data[0] as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $title = $row[1] ?? '';

                if (!$title) {
                    continue;
                }

                // $prompt = config('app.price_prompt') . $title;
                $prompt = config('app.price_prompt') . ' "' . $title . '"';

                sleep(2);
                $result = $chatGptService->getMessage($prompt);

                preg_match(
                    '/Current Price:\s*₹?([\d,]+)/',
                    $result['content'] ?? '',
                    $matches
                );

                $price = isset($matches[1])
                    ? str_replace(',', '', $matches[1])
                    : '';

                $sheet->setCellValue('F' . ($index + 1), $price);
                $sheet->setCellValue('G' . ($index + 1), $result);

                $processed++;
                $progress = intval(($processed / $totalRows) * 100);

                $this->priceFile->update([
                    'progress' => $progress
                ]);
            }

            $this->priceFile->update([
                'status' => 'Completed',
                'progress' => 100,
            ]);

            // $output = 'processed/' . time() . '_' . $this->priceFile->file_name;

            $extension = strtolower(pathinfo($this->priceFile->file_name, PATHINFO_EXTENSION));
            $output = 'processed/' . time() . '_' .
                pathinfo($this->priceFile->file_name, PATHINFO_FILENAME) .
                '.' . $extension;

            switch ($extension) {
                case 'csv':
                    $writer = IOFactory::createWriter($spreadsheet, 'Csv');
                    $writer->setDelimiter(',');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\n");
                    break;

                case 'xls':
                    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
                    break;

                default:
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    break;
            }

            $writer->save(storage_path('app/public/' . $output));

            // IOFactory::createWriter($spreadsheet, 'Xlsx')
            //     ->save(storage_path('app/public/' . $output));

            $this->priceFile->update([
                'status' => 'Completed',
                'processed_file' => $output,
            ]);
        } catch (\Throwable $e) {

            $this->priceFile->update([
                'status' => 'Failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
