<?php

namespace App\Filament\Resources\BukuResource\Pages;

use App\Filament\Resources\BukuResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuku extends CreateRecord
{
    protected static string $resource = BukuResource::class;
    protected function getRedirectUrl(): string
    {
        // Redirect ke halaman list setelah berhasil create
        return $this->getResource()::getUrl('index');
    }
    
}
