<?php

namespace App\Filament\Resources\CaseStudyResource\Pages;

use App\Filament\Resources\CaseStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaseStudy extends EditRecord
{
    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openPublicPage')
                ->label('Abrir no site')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => rtrim((string) config('app.frontend_url'), '/').'/cases/'.$this->getRecord()->slug)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->getRecord()->slug)),
            Actions\DeleteAction::make(),
        ];
    }
}
