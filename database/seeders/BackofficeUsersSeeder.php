<?php

namespace Database\Seeders;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackofficeUsersSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = Negocio::query()
            ->orderBy('id')
            ->get(['id', 'nombre']);

        $businessIds = $businesses
            ->pluck('id')
            ->all();

        foreach ($this->users() as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => User::ROLE_PLATFORM_ADMIN,
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            if ($businessIds !== []) {
                $user->negocios()->syncWithoutDetaching($businessIds);
            }
        }

        User::query()
            ->where('role', User::ROLE_BUSINESS_ADMIN)
            ->where(function ($query) {
                $query
                    ->where('email', 'like', 'demo+%@example.test')
                    ->orWhere('email', 'like', 'admin@%.test');
            })
            ->delete();

        foreach ($businesses as $business) {
            $email = $this->demoEmailForBusiness($business->nombre);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Demo '.$business->nombre,
                    'email' => $email,
                    'role' => User::ROLE_BUSINESS_ADMIN,
                    'password' => Hash::make('demo12345'),
                    'email_verified_at' => now(),
                ]
            );

            $user->negocios()->sync([$business->id]);
        }
    }

    private function demoEmailForBusiness(string $businessName): string
    {
        $aliases = [
            'Bodegas Martín Códax' => 'martincodax',
            'Bodegas Viña Atlántica' => 'vinatlantica',
            'Pazo de Señoráns' => 'senorans',
            'Terras Gauda' => 'terrasgauda',
            'Paco & Lola' => 'pacolola',
            'Palacio de Fefiñanes' => 'fefinanes',
        ];

        $slug = $aliases[$businessName] ?? Str::slug($businessName, '');

        return sprintf('admin@%s.test', $slug);
    }

    private function users(): array
    {
        return [
            [
                'name' => 'Jacobo',
                'email' => 'jacobo@clockia.net',
                'password' => 'jacobo12345',
            ],
            [
                'name' => 'Command',
                'email' => 'command@clockia.net',
                'password' => 'command12345',
            ],
        ];
    }
}
