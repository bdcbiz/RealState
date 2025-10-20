<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hash the password if it's been changed
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Auto-set email_verified_at if is_verified is true and email_verified_at is null
        if (isset($data['is_verified']) && $data['is_verified'] && empty($this->record->email_verified_at)) {
            $data['email_verified_at'] = now();
        }

        // Clear email_verified_at if is_verified is false
        if (isset($data['is_verified']) && !$data['is_verified']) {
            $data['email_verified_at'] = null;
        }

        return $data;
    }
}
