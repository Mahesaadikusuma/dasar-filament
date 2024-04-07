<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Create A Post')
                    ->description('create post over here')

                    ->collapsible()
                    ->schema([
                        TextInput::make('title')
                            ->live(debounce: 800)->required()
                            ->minLength(3)->maxLength(100)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                if (($get('slug') ?? '') !== Str::slug($old)) {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')->readOnly(),

                        // PENGGUNAAN SEPERTI AKAN LAMBAT JIKA MENGELOLA DATA HINGG 10.000
                        // Select::make('category_id')->label('Category')
                        //     ->options(Category::all()->pluck('name', 'id'))
                        //     ->searchable()
                        //     ->required(),

                        // Select::make('category_id')
                        //     ->label('Category')
                        //     ->relationship(name: 'category', titleAttribute: 'name')
                        //     ->searchable()
                        //     ->preload()
                        //     ->required(),


                        ColorPicker::make('color')->required(),
                        MarkdownEditor::make('content')->required()->columnSpanFull(),
                    ])->columnSpan(2)->columns(2),

                Group::make()->schema([
                    Section::make('Image')
                        ->collapsible()
                        ->schema([
                            FileUpload::make('thumbnails')->disk('public')->directory('thumbnail')->required(),

                        ])->columnSpan(1),
                    Section::make('meta')->schema([
                        TagsInput::make('tags')->required(),
                        Checkbox::make('published'),
                    ]),
                ]),

            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnails'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\CheckboxColumn::make('published'),


            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
