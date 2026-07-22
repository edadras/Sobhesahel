<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;

class StatisticsOsChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'نمودار نوع دستگاه امروز'; // Persian Title

    protected static bool $isLazy = false; // Loads immediately
//    protected static ?int $pollingInterval = 30000; // Refresh every 30s

    protected static bool $isDiscovered = false;


    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $matomoUrl = 'http://145.239.138.55:8011/index.php';
        $tokenAuth = 'ba67a38bc8b9095dde20cb9870912764'; // Replace with your valid API token
        $siteId = 1;

        // Fetch today's device type data
        $deviceStats = $this->fetchTodayDeviceStats($matomoUrl, $tokenAuth, $siteId);

        return [
            'datasets' => [
                [
                    'data' => array_values($deviceStats),
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.6)',  // Blue (Desktop)
                        'rgba(255, 99, 132, 0.6)',  // Red (Smartphone)
                        'rgba(255, 206, 86, 0.6)',  // Yellow (Tablet)
                        'rgba(153, 102, 255, 0.6)', // Purple (Feature phone)
                        'rgba(75, 192, 192, 0.6)',  // Teal (Phablet)
                        'rgba(255, 159, 64, 0.6)',  // Orange (Console)
                        'rgba(201, 203, 207, 0.6)', // Grey (TV)
                        'rgba(123, 239, 178, 0.6)', // Green (Smart Display)
                        'rgba(255, 87, 51, 0.6)',   // Red-Orange (Car Browser)
                        'rgba(199, 199, 199, 0.6)', // Light Grey (Unknown)
                    ],
                ],
            ],
            'labels' => array_keys($deviceStats),
        ];
    }

    private function fetchTodayDeviceStats($matomoUrl, $tokenAuth, $siteId)
    {
        $response = Http::get($matomoUrl, [
            'module' => 'API',
            'method' => 'DevicesDetection.getType',
            'idSite' => $siteId,
            'period' => 'day',
            'date' => 'today',
            'format' => 'json',
            'token_auth' => $tokenAuth,
        ]);

        // Default values to ensure all categories are always displayed
        $deviceData = [
            'دسکتاپ' => 0,
            'گوشی هوشمند' => 0,
            'تبلت' => 0,
            'گوشی ساده' => 0,
            'فبلت' => 0,
            'کنسول' => 0,
            'تلویزیون' => 0,
            'نمایشگر هوشمند' => 0,
            'مرورگر خودرو' => 0,
            'نامشخص' => 0,
        ];

        if ($response->successful()) {
            foreach ($response->json() as $device) {
                $label = strtolower($device['label']);
                $visits = $device['nb_visits'] ?? 0;

                if ($label === 'desktop') {
                    $deviceData['دسکتاپ'] += $visits;
                } elseif ($label === 'smartphone') {
                    $deviceData['گوشی هوشمند'] += $visits;
                } elseif ($label === 'tablet') {
                    $deviceData['تبلت'] += $visits;
                } elseif ($label === 'feature phone') {
                    $deviceData['گوشی ساده'] += $visits;
                } elseif ($label === 'phablet') {
                    $deviceData['فبلت'] += $visits;
                } elseif ($label === 'console') {
                    $deviceData['کنسول'] += $visits;
                } elseif ($label === 'tv') {
                    $deviceData['تلویزیون'] += $visits;
                } elseif ($label === 'smart display') {
                    $deviceData['نمایشگر هوشمند'] += $visits;
                } elseif ($label === 'car browser') {
                    $deviceData['مرورگر خودرو'] += $visits;
                } else {
                    $deviceData['نامشخص'] += $visits;
                }
            }
        }

        return $deviceData;
    }
}
