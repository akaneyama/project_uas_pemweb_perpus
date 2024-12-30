<?php

namespace App\Filament\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as AuthRegister;

class Register extends AuthRegister{
    public function form(Form $form):Form {
        return $form ->schema([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),


            TextInput::make('number_phone')
            ->label('Nomot Telepon'),
            TextInput::make('alamat')
            ->label('Alamat'),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ])->statePath('data');
    }
}
