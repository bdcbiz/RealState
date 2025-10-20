<?php

namespace App\Filament\Company\Resources\FalseResource\Pages\Auth;

use App\Filament\Company\Resources\FalseResource;
use Filament\Resources\Pages\Page;

class EditProfile extends Page
{
    protected static string $resource = FalseResource::class;

    protected static string $view = 'filament.company.resources.false-resource.pages.auth.edit-profile';
}
