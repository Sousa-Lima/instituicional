<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->disabled(),
                Forms\Components\TextInput::make('email')->disabled(),
                Forms\Components\TextInput::make('company')->disabled(),
                Forms\Components\TextInput::make('job_title')->disabled(),
                Forms\Components\TextInput::make('interest')->disabled(),
                Forms\Components\TextInput::make('business_stage')->disabled(),
                Forms\Components\Textarea::make('message')->disabled()->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('interest')
                    ->badge(),
                Tables\Columns\IconColumn::make('consent_lgpd')
                    ->boolean()
                    ->label('LGPD'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('interest')
                    ->options([
                        'process' => 'Process',
                        'software' => 'Software',
                        'cloud' => 'Cloud',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'view' => Pages\ViewLead::route('/{record}'),
        ];
    }
}
