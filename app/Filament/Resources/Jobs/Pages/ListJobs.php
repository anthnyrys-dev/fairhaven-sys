<?php

namespace App\Filament\Resources\Jobs\Pages;

use App\Enums\JobStatus;
use App\Filament\Resources\Jobs\JobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'outstanding' => Tab::make('Outstanding')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', JobStatus::COMPLETE->value))
                ->badge(fn () => JobResource::getModel()::where('status', '!=', JobStatus::COMPLETE->value)->count()),
            'all' => Tab::make('All Jobs'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'outstanding';
    }
}
