<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use App\Models\Shop\Order;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders per month';

    protected static ?int $sort = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $months = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $latestMonthOrder = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as order_count')
            ->where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereNull('deleted_at')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->groupBy('year', 'month')
            ->get();

        foreach ($latestMonthOrder as $order) {
            if (isset($months[$order->month - 1])) {
                $months[$order->month - 1] = $order->order_count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $months,
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
