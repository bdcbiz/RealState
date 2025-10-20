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
                    ->label('Company Logo')
                    // Company IS the authenticated user, so check if user exists
                    ->visible(fn() => auth()->check())
                    ->imagePreviewHeight('250')
                    ->maxSize(5120)
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file): string => time() . '_' . str()->random(10) . '.' . $file->getClientOriginalExtension()
                    )
                    ->disk('public')
                    ->directory('company-logos')
                    ->visibility('public')
                    ->dehydrateStateUsing(function ($state) {
                        // When saving, if it's just a path, keep it; observer will convert to URL
                        return $state;
                    })
                    ->formatStateUsing(function ($state, $record) {
                        // When displaying, if it's a URL, extract the path for Filament
                        if ($state && str_starts_with($state, 'http')) {
                            $filename = basename($state);
                            return 'company-logos/' . $filename;
                        }
                        return $state;
                    }),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
