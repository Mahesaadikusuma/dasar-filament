<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class authorsRelationManager extends RelationManager
{
    protected static string $relationship = 'authors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput::make('name')->required()->minLength(2)
                //     ->maxLength(255),
                // TextInput::make('email')->required()->email(),
                // TextInput::make('password')->required()->visibleOn('create')
                //     ->password()
                //     ->revealable()

                TextInput::make('order')->numeric()->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->icon('heroicon-m-users'),
                TextColumn::make('email')->icon('heroicon-m-envelope')->iconColor('primary')
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('order'),
                TextColumn::make('created_at')
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                // Tables\Actions\AttachAction::make(),
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('order')->numeric()->required(),
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
