<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseStudyResource\Pages;
use App\Models\CaseStudy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CaseStudyResource extends Resource
{
    protected static ?string $model = CaseStudy::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->helperText('Opcional. Se vazio, será gerado automaticamente.'),
                Forms\Components\TextInput::make('customer_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sector')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft'),
                Forms\Components\Toggle::make('featured')
                    ->default(false),
                Forms\Components\Textarea::make('short_summary')
                    ->required()
                    ->rows(4)
                    ->maxLength(10000)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content_html')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metrics')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('main_image')
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Ex.: url, alt, width, height'),
                Forms\Components\KeyValue::make('seo')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sector')
                    ->label('Setor')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Destaque'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseStudies::route('/'),
            'create' => Pages\CreateCaseStudy::route('/create'),
            'edit' => Pages\EditCaseStudy::route('/{record}/edit'),
        ];
    }
}
