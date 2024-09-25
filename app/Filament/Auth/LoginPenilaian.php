<?php

namespace App\Filament\Auth;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Validation\ValidationException;



class LoginPenilaian extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return Select::make('login')
            ->label('Pilih Cabang')
            ->native(false)
            ->options([
                'Tartil' => 'Tartil',
                'Tilawah Anak-anak' => 'Tilawah Anak-anak',
                'Tilawah Remaja' => 'Tilawah Remaja',
                'Tilawah Dewasa' => 'Tilawah Dewasa',
                'MHQ 1 Juz dan Tilawah' => 'MHQ 1 Juz dan Tilawah',
                'MHQ 5 Juz dan Tilawah' => 'MHQ 5 Juz dan Tilawah',
                'MHQ 10 Juz dan Tilawah' => 'MHQ 10 Juz dan Tilawah',
                'MHQ 20 Juz dan Tilawah' => 'MHQ 20 Juz dan Tilawah',
                'MHQ 30 Juz dan Tilawah' => 'MHQ 30 Juz dan Tilawah',
                'MFQ' => 'MFQ',
                'MSQ' => 'MSQ',
                'MKQ Naskah' => 'MKQ Naskah',
                'MKQ Hiasan' => 'MKQ Hiasan',
            ])
            ->required();
            // ->autocomplete()
            // ->autofocus()
            // ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
           $login_type = 'name';

        return [
            $login_type => $data['login'],
            'password'  => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
