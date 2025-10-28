<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;

class DataExport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.data-export';

    protected static ?string $navigationLabel = 'Export Data';

    protected static ?string $title = 'Export Data';

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportComprehensiveData')
                ->label('Export All Data (Units + Compounds + Companies)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => route('export.comprehensive-data'))
                ->openUrlInNewTab(),
        ];
    }
}
