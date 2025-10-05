<?php

namespace App\Filament\Company\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Name'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->label('Phone Number'),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('user-images')
                    ->label('Profile Image'),
                FileUpload::make('company.logo')
                    ->image()
                    ->disk('public')
                    ->directory('company-images')
                    ->label('Company Logo')
                    ->visible(fn() => auth()->user()->company_id !== null),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
