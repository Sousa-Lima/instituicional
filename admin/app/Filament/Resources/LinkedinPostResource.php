<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkedinPostResource\Pages;
use App\Jobs\PublishLinkedinPost;
use App\Models\LinkedinPost;
use Illuminate\Support\HtmlString;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LinkedinPostResource extends Resource
{
    protected static ?string $model = LinkedinPost::class;

    protected static ?int $navigationSort = 31;

    protected static ?string $modelLabel = 'Post LinkedIn';

    protected static ?string $pluralModelLabel = 'Posts LinkedIn';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-briefcase';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Social';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->label('Título para o blog (opcional)')
                    ->maxLength(180)
                    ->nullable()
                    ->columnSpanFull()
                    ->helperText('Não é enviado ao LinkedIn. Se preenchido, será usado como título no blog.'),
                Forms\Components\Textarea::make('excerpt')
                    ->label('Resumo para o blog (opcional)')
                    ->rows(3)
                    ->maxLength(500)
                    ->nullable()
                    ->columnSpanFull()
                    ->helperText('Não é enviado ao LinkedIn. Se preenchido, será usado como resumo no blog.'),
                Forms\Components\Textarea::make('text')
                    ->label('Texto do post')
                    ->required()
                    ->rows(8)
                    ->maxLength(3000)
                    ->columnSpanFull()
                    ->helperText('Máx. 3000 caracteres. Suporta emojis e quebras de linha.'),
                Forms\Components\Select::make('publish_target')
                    ->label('Publicar em')
                    ->options([
                        'personal' => 'Perfil pessoal',
                        'company'  => 'Página da empresa',
                    ])
                    ->default('personal')
                    ->required()
                    ->native(false)
                    ->helperText('Escolha o destino desta publicação.'),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem (opcional)')
                    ->image()
                    ->nullable()
                    ->disk('public')
                    ->directory('linkedin')
                    ->maxSize(10240) // 10 MB — limite LinkedIn
                    ->columnSpanFull()
                    ->helperText('Opcional. JPG/PNG, máx. 10 MB. Proporção recomendada: 1.91:1 (1200×628px).'),
                Forms\Components\TextInput::make('image_title')
                    ->label('Título da imagem')
                    ->maxLength(200)
                    ->nullable()
                    ->helperText('Texto alternativo exibido pela LinkedIn na imagem.')
                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => filled($get('image_path'))),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Agendar para')
                    ->nullable()
                    ->seconds(false)
                    ->timezone('America/Sao_Paulo')
                    ->minDate(fn (): \Illuminate\Support\Carbon => now()->addMinutes(5)->startOfMinute())
                    ->disabled(fn (?LinkedinPost $record): bool => $record?->status === 'published')
                    ->dehydrated(fn (?LinkedinPost $record): bool => $record?->status !== 'published')
                    ->visible(fn (?LinkedinPost $record): bool => $record?->status !== 'published')
                    ->helperText('Deixe em branco para salvar como rascunho.'),
                Forms\Components\Placeholder::make('status_label')
                    ->label('Status')
                    ->content(fn (?LinkedinPost $record): string => match ($record?->status) {
                        'draft'     => 'Rascunho',
                        'scheduled' => 'Agendado para ' . $record?->scheduled_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                        'published' => 'Publicado em ' . $record?->published_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                        'failed'    => 'Falhou: ' . $record?->error_message,
                        default     => '-',
                    })
                    ->visibleOn('edit'),
                Forms\Components\Placeholder::make('published_link_label')
                    ->label('Link do post publicado')
                    ->content(fn (?LinkedinPost $record): HtmlString => new HtmlString(
                        blank($record?->linkedin_post_url)
                            ? '—'
                            : '<a href="'.e((string) $record->linkedin_post_url).'" target="_blank" rel="noopener noreferrer">Abrir no LinkedIn</a>'
                    ))
                    ->visibleOn('edit')
                    ->hidden(fn (?LinkedinPost $record): bool => blank($record?->linkedin_post_url))
                    ->columnSpanFull(),
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
                    ->height(60)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título blog')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Texto')
                    ->limit(100)
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
                Tables\Columns\TextColumn::make('publish_target')
                    ->label('Destino')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'company' ? 'info' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'company' ? 'Empresa' : 'Pessoal'),
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
                Tables\Columns\TextColumn::make('linkedin_post_url')
                    ->label('Link do post')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Abrir' : '—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('publish_target')
                    ->label('Destino')
                    ->options([
                        'personal' => 'Pessoal',
                        'company'  => 'Empresa',
                    ]),
            ])
            ->actions([
                Actions\Action::make('publish_now')
                    ->label('Publicar agora')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (LinkedinPost $record): bool => $record->isPending())
                    ->action(function (LinkedinPost $record): void {
                        $record->update(['status' => 'scheduled', 'scheduled_at' => now()]);
                        PublishLinkedinPost::dispatch($record->id);

                        Notification::make()
                            ->title('Post enviado para publicação!')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('republish')
                    ->label('Republicar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (LinkedinPost $record): bool => in_array($record->status, ['published', 'failed'], true))
                    ->action(function (LinkedinPost $record): void {
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
                    }),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->visible(fn (LinkedinPost $record): bool => $record->status !== 'published'),
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
            'index'  => Pages\ListLinkedinPosts::route('/'),
            'create' => Pages\CreateLinkedinPost::route('/create'),
            'edit'   => Pages\EditLinkedinPost::route('/{record}/edit'),
        ];
    }
}
