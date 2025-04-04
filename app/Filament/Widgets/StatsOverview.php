<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getColumns(): int {
        return 2;
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Dokter', User::role('dokter')->count())
                ->description('Jumlah seluruh dokter')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
                
            Stat::make('Total Pasien', User::role('pasien')->count())
                ->description('Jumlah seluruh pasien')
                ->descriptionIcon('heroicon-o-users')
                ->color('secondary'),
        ];
    }
}
