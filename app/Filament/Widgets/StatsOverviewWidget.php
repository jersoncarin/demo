<?php

namespace App\Filament\Widgets;

use App\Models\Shop\Order;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {

        $startDate = !is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = !is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        $currentWeekStartDate = $startDate ? $endDate->copy()->startOfWeek() : Carbon::now()->startOfWeek();
        $currentWeekEndDate = $startDate ? $endDate->copy()->endOfWeek() : Carbon::now()->endOfWeek();
        $previousWeekStartDate = $startDate ? $endDate->copy()->subWeek()->startOfWeek() : Carbon::now()->startOfWeek()->subWeek();
        $previousWeekEndDate = $startDate ? $endDate->copy()->subWeek()->endOfWeek() : Carbon::now()->startOfWeek()->subDay();

        $currentWeekSum = Order::with('items')
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$currentWeekStartDate, $currentWeekEndDate])
            ->sum(DB::raw('(select sum(qty * unit_price) from shop_order_items where shop_orders.id = shop_order_items.shop_order_id)'));

        $previousWeekSum = Order::with('items')
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$previousWeekStartDate, $previousWeekEndDate])
            ->sum(DB::raw('(select sum(qty * unit_price) from shop_order_items where shop_orders.id = shop_order_items.shop_order_id)'));

        $diffRev = $currentWeekSum - $previousWeekSum;


        if (is_null($startDate)) {
            $startDate = $endDate->copy()->startOfDay();
        }


        $currentDayCustomer = Order::selectRaw('DISTINCT shop_customer_id, created_at')
            ->where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereRaw('DATE(created_at) = DATE(?)', [$startDate])
            ->count();

        $previousDayCustomer = Order::selectRaw('DISTINCT shop_customer_id, created_at')
            ->where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereRaw('DATE(created_at) < DATE(?)', [$startDate])
            ->count();

        $currentDayOrder = Order::selectRaw('shop_customer_id, created_at')
            ->where('user_id', auth()->id())
            ->whereRaw('DATE(created_at) = DATE(?)', [$startDate])
            ->count();

        $previousDayOrder = Order::selectRaw('shop_customer_id, created_at')
            ->where('user_id', auth()->id())
            ->whereRaw('DATE(created_at) < DATE(?)', [$startDate])
            ->count();


        $formatNumber = function (int $number): string {
            if ($number < 1000) {
                return (string) Number::format($number, 0);
            }

            if ($number < 1000000) {
                return Number::format($number / 1000, 2) . 'k';
            }

            return Number::format($number / 1000000, 2) . 'm';
        };

        $diffCus = $currentDayCustomer - $previousDayCustomer;
        $diffOrd = $currentDayOrder - $previousDayOrder;

        return [
            Stat::make('Revenue', '₱' . $formatNumber($currentWeekSum + $previousWeekSum))
                ->description($formatNumber(abs($diffRev)) . " this week from " . $formatNumber($previousWeekSum))
                ->descriptionIcon($diffRev < 0 ? "heroicon-m-arrow-trending-down" : 'heroicon-m-arrow-trending-up')
                ->chart([$previousWeekSum, $currentWeekSum])
                ->color($diffRev < 0 ? 'danger' : 'success'),
            Stat::make('New customers', $formatNumber($previousDayCustomer + $currentDayCustomer))
                ->description($diffCus < 0 ? "No increase this day" : ($formatNumber($diffCus) . ' increase this day'))
                ->descriptionIcon($diffCus < 0 ? "heroicon-m-arrow-trending-down" : 'heroicon-m-arrow-trending-up')
                ->chart([$previousDayCustomer, $currentDayCustomer])
                ->color($diffCus < 0 ? 'danger' : 'success'),
            Stat::make('New orders', $formatNumber($previousDayOrder + $currentDayOrder))
                ->description($diffOrd < 0 ? "No increase this day" : ($formatNumber($diffCus) . ' increase this day'))
                ->descriptionIcon($diffOrd < 0 ? "heroicon-m-arrow-trending-down" : 'heroicon-m-arrow-trending-up')
                ->chart([$previousDayOrder, $currentDayOrder])
                ->color($diffOrd < 0 ? 'danger' : 'success'),
        ];
    }
}
