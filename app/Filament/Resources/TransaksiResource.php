<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Models\Buku;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Validation\ValidationException;  // Correct import
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('User')
                    ->required()
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder(auth()->user()->role === 'ADMIN' ? 'Pilih user...' : null)
                    ->default(fn () => auth()->id())
                    ->disabled(fn () => auth()->user()->role === 'USER')
                    ->visible(fn () => auth()->check()),
                Forms\Components\Select::make('id_buku')
                    ->relationship('buku', 'id_buku')
                    ->searchable()
                    ->placeholder('Pilih Buku')
                    ->label('Buku')
                    ->required()
                    ->options(function () {
                        return Buku::all()->pluck('nama_buku', 'id_buku');
                    }),
                    Forms\Components\TextInput::make('jumlah_peminjaman')
                    ->numeric()
                    ->required()
                    ->label('Jumlah Peminjaman')
                    ,
                Forms\Components\DatePicker::make('tanggal_transaksi')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_pengembalian')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->default('DIPINJAM')
                    ->visible(fn () => auth()->user()->role === 'ADMIN')
                    ->options([
                        'DIPINJAM' => 'DIPINJAM',
                        'DIKEMBALIKAN' => 'DIKEMBAlIKAN'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('buku.nama_buku')
                    ->label('Nama Buku')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_peminjaman')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pengembalian')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
