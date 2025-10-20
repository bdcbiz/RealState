<?php

namespace App\Filament\Company\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->label('Phone Number'),
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('user-images')
                            ->label('Profile Image'),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['role'] = 'company';

        return $data;
    }
}
