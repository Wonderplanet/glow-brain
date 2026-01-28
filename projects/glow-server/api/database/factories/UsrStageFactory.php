<?php

namespace Database\Factories;

use App\Domain\Stage\Models\Eloquent\UsrStage;
use App\Domain\Stage\Models\UsrStage as ModelsUsrStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Stage\Models\UsrStage>
 */
class UsrStageFactory extends Factory
{

    protected $model = UsrStage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid(),
            'usr_user_id' => fake()->uuid(),
            'mst_stage_id' => 1,
            'clear_count' => 1,
            'clear_time_ms' => null,
        ];
    }

    public function createManyAndConvert(int|iterable|null $records = null): Collection
    {
        return $this->createMany($records)->map(
            fn (UsrStage $model) => ModelsUsrStage::createFromRecord((object) $model->toArray()),
        );
    }

    public function createAndConvert(array $attributes = [], ?UsrStage $parent = null): ModelsUsrStage
    {
        return ModelsUsrStage::createFromRecord(
            (object) $this->create($attributes, $parent)->toArray(),
        );
    }
}
