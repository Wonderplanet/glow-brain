<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstNgWord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstNgWord>
 */
class MstNgWordFactory extends Factory
{
    protected $model = MstNgWord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'word' => fake()->uuid(),
            'release_key' => 1
        ];
    }
}
