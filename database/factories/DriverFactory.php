<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Profile;
use App\Models\Secretariat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => $pass = fake()->password(8),
            'password_confirmation' => $pass,
            'cpf' => fake()->cpf(false),
            'cnh' => fake()->unique()->numerify('###########'),
            'cnh_category' => fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE']),
            'cnh_expiration_date' => fake()
                ->dateTimeBetween('tomorrow', '+1 year')
                ->format('Y-m-d'),
            'phone_number' => fake()->e164PhoneNumber(),
            'profile_id' => Profile::create(['name' => 'test', 'description' => 'test'])->id,
            'secretariat_id' => Secretariat::create(['name' => 'test', 'acronym' => 'test'])->id,
        ];
    }
}
