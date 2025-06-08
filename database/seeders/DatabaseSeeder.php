<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Punishment;
use App\Models\RentalPeriod;
use App\Models\Rolador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categorias = Category::factory(4)->create(new Sequence(
            ['name' => 'Joyeria'],
            ['name' => 'Servicios'],
            ['name' => 'Textiles'],
            ['name' => 'Aliments']
        ));

        $roladores = Rolador::factory(30)->create(new Sequence(fn() => [
            'photo' => fake()->randomElement(Storage::disk('public')->files('roladores')),
            'category_id' => fake()->randomElement($categorias)->id
        ]));

        foreach ($roladores as $rolador) {
            $date = fake()->dateTimeBetween('-2 years', '+1 months');
            while (!Date::now()->isBefore($date)) {
                $endDate = Date::parse($date)->addWeeks(rand(1, 3));
                $lastPunishment = Punishment::factory()->for($rolador)->create([
                    'start_date' => $date,
                    'end_date' => $endDate
                ]);
                $date = fake()->dateTimeBetween($endDate->addDay(), '+6 months');
            }
            if (fake()->boolean(10) && !Date::now()->isBefore($lastPunishment->end_date)) {
                Punishment::factory()->for($rolador)->current($lastPunishment->end_date)->create();
            }

            $numWeeks = rand(1, 3);
            $date = fake()->dateTimeBetween("-{$numWeeks} weeks", 'now');
            while (!Date::now()->subDays(2)->isBefore($date)) {
                $endDate = Date::parse($date)->addWeek();
                RentalPeriod::factory()->for($rolador)->create([
                    'start_date' => $date,
                    'end_date' => $endDate
                ]);
                $date = $endDate->addDay();
            }
        }
    }

    private function generateRoladorPhoto()
    {
        $response = Http::get('https://randomuser.me/api/');
        $data = $response->json()['results'][0];

        $imageUrl = $data['picture']['large']; // URL de la imagen de la persona
        $filename = basename($imageUrl); // Obtener el nombre de archivo

        // Descargar la imagen y guardarla en storage/app/public/avatars
        $contents = file_get_contents($imageUrl);
        Storage::disk('public')->put('roladores/' . $filename, $contents);
        return 'roladores/' . $filename;
    }
}
