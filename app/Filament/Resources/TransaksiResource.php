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
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Daftar Transaksi';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?string $navigationGroup = 'Peminjaman';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('User')
                    ->required()
                    ->options(function () {
                        // Jika role adalah USER, hanya tampilkan dirinya sendiri
                        if (auth()->user()->role === 'USER') {
                            return User::where('id', auth()->id())->pluck('name', 'id');
                        }

                        // Jika role adalah ADMIN, tampilkan semua pengguna
                        return User::pluck('name', 'id');
                    })
                    ->searchable(auth()->user()->role === 'ADMIN')
                    ->placeholder(auth()->user()->role === 'ADMIN' ? 'Pilih user...' : null)
                    ->default(fn () => auth()->id())
                    ->visible(fn () => auth()->check()),
                Forms\Components\Select::make('id_buku')
                    ->relationship('buku', 'id_buku')
                    ->searchable()
                    ->placeholder('Pilih Buku')
                    ->label('Buku')
                    ->required()
                    ->options(function () {
                        return Buku::all()->pluck('nama_buku', 'id_buku');
                    })
                    ,
                    Forms\Components\TextInput::make('jumlah_peminjaman')
                    ->numeric()
                    ->required()
                    ->label('Jumlah Peminjaman')
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $idBuku = $get('id_buku');
                        $buku = Buku::find($idBuku);

                        if ($buku && $state > $buku->jumlah_buku) {

                            Notification::make()
                                ->danger()
                                ->title('Jumlah buku tidak mencukupi')
                                ->body('Jumlah buku yang tersedia tidak mencukupi.')
                                ->send();

                            return "Jumlah buku yang tersedia tidak mencukupi.";
                        }

                        return null;
                    }),
                Forms\Components\DatePicker::make('tanggal_transaksi')
                    ->required()
                    ->default(Carbon::today()),
                Forms\Components\DatePicker::make('tanggal_pengembalian')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->default('DIPINJAM')
                    ->visible(fn () => auth()->user()->role === 'ADMIN')
                    ->options([
                        'DIPINJAM' => 'DIPINJAM',
                        'DIKEMBALIKAN' => 'DIKEMBAlIKAN'
                    ])
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        // Statusnya telah berubah, model Transaksi akan menangani pengembalian buku
                        $transaksi = $get('record'); // Ambil data transaksi saat ini

                        if (!$transaksi) {

                            return;
                        }

                        $transaksi->status = $state; // Update status transaksi
                        $transaksi->save();
                    }),
                ]);


    }


    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('user.name')
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
