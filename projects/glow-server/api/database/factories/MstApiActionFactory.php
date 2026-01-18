<?php

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstApiAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Models\MstApiAction>
 */
class MstApiActionFactory extends Factory
{
    protected $model = MstApiAction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => 'aaa',
            'api_path' => 'bbb',
            'through_app' => 0,
            'through_master' => 0,
            'through_asset' => 0,
            'through_date' => 0,
            'release_key' => 0,
        ];
    }
}
