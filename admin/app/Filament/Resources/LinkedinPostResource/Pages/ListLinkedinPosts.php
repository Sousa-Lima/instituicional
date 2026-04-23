<?php

namespace App\Filament\Resources\LinkedinPostResource\Pages;

use App\Filament\Resources\LinkedinPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLinkedinPosts extends ListRecords
{
    protected static string $resource = LinkedinPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo post'),
        ];
    }
}
