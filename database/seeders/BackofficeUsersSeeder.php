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

        foreach ($businesses as $business) {
            $email = sprintf(
                'demo+%s-%d@example.test',
                Str::slug($business->nombre, '-'),
                $business->id
            );

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
