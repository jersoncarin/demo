<?php

namespace App\Filament\Widgets;

use App\Models\Shop\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class CustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Total customers';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $months = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        $latestMonthCustomers = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(DISTINCT shop_customer_id) as distinct_customers')
            ->where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereNull('deleted_at')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->groupBy('year', 'month')
            ->get();

        foreach ($latestMonthCustomers as $customer) {
            if (isset($months[$customer->month - 1])) {
                $months[$customer->month - 1] = $customer->distinct_customers;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => $months,
                    'fill' => 'start',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
