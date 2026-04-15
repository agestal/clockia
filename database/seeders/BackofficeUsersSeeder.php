<?php

namespace Database\Seeders;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BackofficeUsersSeeder extends Seeder
{
    public function run(): void
    {
        $businessIds = Negocio::query()
            ->pluck('id')
            ->all();

        foreach ($this->users() as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            if ($businessIds !== []) {
                $user->negocios()->syncWithoutDetaching($businessIds);
            }
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
