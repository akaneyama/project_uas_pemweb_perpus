<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Transaksi;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        // Ambil pengguna yang sedang login
        $user = auth()->user();

        // Jika pengguna adalah USER, tampilkan hanya transaksinya sendiri
        if ($user->role === 'USER') {
            return static::getResource()::getModel()::query()->where('id', $user->id);
        }

        // Jika ADMIN, tampilkan semua data transaksi
        return parent::getTableQuery();
    }

}
