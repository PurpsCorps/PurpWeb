<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        Log::info('getHeaderActions called');

        return [
            Actions\Action::make('print_receipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    Log::info('Print receipt action triggered');
                    // Implementasi logika print receipt di sini
                    try {
                        Log::info('Starting PDF generation process');

                        $rawData = $this->record->toArray();
                        Log::info('Raw data:', ['data' => json_encode($rawData, JSON_INVALID_UTF8_SUBSTITUTE)]);

                        $sanitizedData = $this->sanitizeData($rawData);
                        Log::info('Sanitized data:', ['data' => json_encode($sanitizedData, JSON_INVALID_UTF8_SUBSTITUTE)]);

                        $order = $sanitizedData;

                        // Implementasi logika generate PDF di sini

                        Log::info('PDF generated successfully');

                        // Return response PDF
                    } catch (\Exception $e) {
                        Log::error('PDF generation error: ' . $e->getMessage());
                        Log::error('Exception trace: ' . $e->getTraceAsString());

                        // Notification logic here
                    }
                })
                ->openUrlInNewTab(),
        ];
    }

    private function sanitizeData($data)
    {
        if (is_string($data)) {
            // Convert to UTF-8 and remove invalid characters
            $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true));

            // Replace any remaining non-printable characters with a space
            $data = preg_replace('/[\x00-\x1F\x7F-\xFF]/u', ' ', $data);

            // Ensure the string is valid UTF-8
            $data = iconv('UTF-8', 'UTF-8//IGNORE', $data);

            return $data;
        }
        if (is_array($data)) {
            return array_map([$this, 'sanitizeData'], $data);
        }
        return $data;
    }
}