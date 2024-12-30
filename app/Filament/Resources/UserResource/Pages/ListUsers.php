<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

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
