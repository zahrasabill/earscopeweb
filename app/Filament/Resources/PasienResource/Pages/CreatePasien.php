<?php

namespace App\Filament\Resources\PasienResource\Pages;

use App\Filament\Resources\PasienResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
class CreatePasien extends CreateRecord
{
    protected static string $resource = PasienResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $defaultPassword = str_replace('-', '', $data['tanggal_lahir']);
        $data['password'] = Hash::make($defaultPassword);

        if(!isset($data['name'])) {
            $data['name'] = $data['kode'];
        }

        if(!isset($data['kode_akses'])) {
            // generate random 5 character alphanumeric code
            $data['kode_akses'] = Str::random(5);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // get role id
        $role = Role::where('name', 'pasien')->first();

        $user = User::find($this->record->id);
        if($user) {
            $user->roles()->attach($role->id);
        }
    }
}
