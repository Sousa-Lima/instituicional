<?php

namespace App\Filament\Resources\InstagramPostResource\Pages;

use App\Filament\Resources\InstagramPostResource;
use App\Jobs\PublishInstagramPost;
use Filament\Resources\Pages\CreateRecord;

class CreateInstagramPost extends CreateRecord
{
    protected static string $resource = InstagramPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Se scheduled_at foi preenchido agendamos; caso contrário, rascunho.
        if (! empty($data['scheduled_at'])) {
            $data['status'] = 'scheduled';
        } else {
            $data['status'] = 'draft';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if ($record->status === 'scheduled' && $record->scheduled_at !== null) {
            $delay = max(0, now()->diffInSeconds($record->scheduled_at, false));
            PublishInstagramPost::dispatch($record->id)->delay($delay);
        }
    }
}
