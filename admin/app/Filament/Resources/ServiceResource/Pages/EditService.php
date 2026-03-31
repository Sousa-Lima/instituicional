<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openPublicPage')
                ->label('Abrir no site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => rtrim((string) config('app.frontend_url'), '/').'/servicos/'.$this->getRecord()->slug)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->getRecord()->slug)),
            Actions\DeleteAction::make(),
        ];
    }
}
