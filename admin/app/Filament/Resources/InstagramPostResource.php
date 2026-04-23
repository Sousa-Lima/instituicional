<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstagramPostResource\Pages;
use App\Jobs\PublishInstagramPost;
use App\Models\InstagramPost;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InstagramPostResource extends Resource
{
    protected static ?string $model = InstagramPost::class;

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Post Instagram';

    protected static ?string $pluralModelLabel = 'Posts Instagram';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-camera';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Social';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('instagram')
                    ->maxSize(8192) // 8 MB — limite Meta API
                    ->columnSpanFull()
                    ->helperText('Mínimo 320px. Formatos: JPG/PNG. Proporções suportadas: 1:1, 4:5, 1.91:1.'),
                Forms\Components\Textarea::make('caption')
                    ->label('Legenda')
                    ->required()
                    ->rows(6)
                    ->maxLength(2200)
                    ->columnSpanFull()
                    ->helperText('Máx. 2200 caracteres. Hashtags e menções são suportados.'),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Agendar para')
                    ->nullable()
                    ->seconds(false)
                    ->timezone('America/Sao_Paulo')
                    ->minDate(now()->addMinutes(5))
                    ->helperText('Deixe em branco para salvar como rascunho e publicar manualmente depois.'),
                Forms\Components\Placeholder::make('status_label')
                    ->label('Status')
                    ->content(fn (InstagramPost $record): string => match ($record->status) {
                        'draft'      => 'Rascunho',
                        'scheduled'  => 'Agendado para ' . $record->scheduled_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                        'published'  => 'Publicado em ' . $record->published_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                        'failed'     => 'Falhou: ' . $record->error_message,
                        default      => '-',
                    })
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagem')
                    ->disk('public')
                    ->square()
                    ->width(60)
                    ->height(60),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Legenda')
                    ->limit(80)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        'failed'    => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Rascunho',
                        'scheduled' => 'Agendado',
                        'published' => 'Publicado',
                        'failed'    => 'Falhou',
                    }),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Agendado para')
                    ->dateTime('d/m/Y H:i', 'America/Sao_Paulo')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado em')
                    ->dateTime('d/m/Y H:i', 'America/Sao_Paulo')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i', 'America/Sao_Paulo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Rascunho',
                        'scheduled' => 'Agendado',
                        'published' => 'Publicado',
                        'failed'    => 'Falhou',
                    ]),
            ])
            ->actions([
                Actions\Action::make('publish_now')
                    ->label('Publicar agora')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (InstagramPost $record): bool => $record->isPending())
                    ->action(function (InstagramPost $record): void {
                        $record->update(['status' => 'scheduled', 'scheduled_at' => now()]);
                        PublishInstagramPost::dispatch($record->id);

                        Notification::make()
                            ->title('Post enviado para publicação!')
                            ->success()
                            ->send();
                    }),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->visible(fn (InstagramPost $record): bool => $record->status !== 'published'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInstagramPosts::route('/'),
            'create' => Pages\CreateInstagramPost::route('/create'),
            'edit'   => Pages\EditInstagramPost::route('/{record}/edit'),
        ];
    }
}
