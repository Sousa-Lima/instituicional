<?php

namespace App\Filament\Resources\InstagramPostResource\Pages;

use App\Filament\Resources\InstagramPostResource;
use App\Jobs\PublishInstagramPost;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstagramPost extends EditRecord
{
    protected static string $resource = InstagramPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status !== 'published'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        // Só permite editar se ainda não foi publicado
        if ($record->status === 'published') {
            return $data;
        }

        if (! empty($data['scheduled_at'])) {
            $data['status'] = 'scheduled';
        } else {
            $data['status'] = 'draft';
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->fresh();

        if ($record->status === 'scheduled' && $record->scheduled_at !== null) {
            // Reagenda: dispara novo job com delay recalculado
            $delay = max(0, now()->diffInSeconds($record->scheduled_at, false));
            PublishInstagramPost::dispatch($record->id)->delay($delay);
        }
    }
}
