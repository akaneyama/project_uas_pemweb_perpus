<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = 'Daftar User';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Kelola';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpan(2)
                    ->maxLength(255),

                    Forms\Components\TextInput::make('number_phone')
                    ->required()
                    ->label('nomor telp')
                    ->numeric()
                    ->maxLength(20),
                Forms\Components\TextInput::make('alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser),
                Forms\Components\Select::make('role')
                    ->options(User::ROLES)
                    ->visible(fn () => auth()->user()->role === 'ADMIN')
                    ->placeholder('Pilih Role')
                    ,

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(auth()->user()->role === 'ADMIN'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(auth()->user()->role === 'ADMIN'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(auth()->user()->role === 'ADMIN')
                    ->visible(fn () => auth()->user()->role === 'ADMIN')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(auth()->user()->role === 'ADMIN')
                    ->visible(fn () => auth()->user()->role === 'ADMIN')
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('number_phone')
                    ->sortable()
                    ->label('Nomor Telepon')
                    ->searchable(auth()->user()->role === 'ADMIN'),
                    Tables\Columns\TextColumn::make('alamat')
                    ->sortable()
                    ->label('alamat')
                    ->searchable(auth()->user()->role === 'ADMIN'),
                    Tables\Columns\TextColumn::make('role')
                    ->visible(fn () => auth()->user()->role === 'ADMIN'),
                    ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
