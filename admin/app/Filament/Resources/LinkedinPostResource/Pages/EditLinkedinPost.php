<?php

namespace App\Filament\Resources\LinkedinPostResource\Pages;

use App\Filament\Resources\LinkedinPostResource;
use App\Jobs\PublishLinkedinPost;
use App\Models\LinkedinPost;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLinkedinPost extends EditRecord
{
    protected static string $resource = LinkedinPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('republish')
                ->label('Republicar')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->getRecord()->status, ['published', 'failed'], true))
                ->action(function (): void {
                    /** @var LinkedinPost $record */
                    $record = $this->getRecord();

                    $record->update([
                        'status' => 'scheduled',
                        'scheduled_at' => now(),
                        'published_at' => null,
                        'linkedin_post_id' => null,
                        'linkedin_post_url' => null,
                        'error_message' => null,
                    ]);

                    PublishLinkedinPost::dispatch($record->id);

                    Notification::make()
                        ->title('Post enviado para republicação!')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->status !== 'published'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($record->status === 'published') {
            return $data;
        }

        $data['status'] = ! empty($data['scheduled_at']) ? 'scheduled' : 'draft';

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->fresh();

        if ($record->status === 'scheduled' && $record->scheduled_at !== null) {
            $delay = max(0, now()->diffInSeconds($record->scheduled_at, false));
            PublishLinkedinPost::dispatch($record->id)->delay($delay);
        }
    }
}
