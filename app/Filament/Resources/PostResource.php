<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\RelationManagers\PostsRelationManager;
use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
// use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\PostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Post Category';


    public static function form(Form $form): Form
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

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship(name: 'category', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->required(),


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

                    Section::make('Authors')->schema([
                        CheckboxList::make('authors')
                            ->label('Co Author')
                            ->relationship(name: 'authors', titleAttribute: 'name')
                            // ->multiple()
                            ->searchable()
                        // ->preload(),

                    ]),
                ]),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('thumbnails')->toggleable(),
                ColorColumn::make('color')->toggleable(),
                TextColumn::make('title')->sortable()->searchable()->toggleable(),
                TextColumn::make('slug')->sortable()->searchable()->toggleable(),
                TextColumn::make('category.name')->sortable()->searchable()->toggleable(),
                TextColumn::make('tags')->toggleable(),
                TextColumn::make('created_at')
                    ->label('published on')
                    // ->since(),
                    ->date()
                    ->sortable()->searchable()->toggleable(),
                CheckboxColumn::make('published')->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User deleted')
                            ->body('The user has been deleted successfully.'),
                    ),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\authorsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
