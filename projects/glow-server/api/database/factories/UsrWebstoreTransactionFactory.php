<?php

namespace Database\Factories;

use App\Domain\Shop\Constants\WebStoreConstant;
use App\Domain\Shop\Models\UsrWebstoreTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Shop\Models\UsrWebstoreTransaction>
 */
class UsrWebstoreTransactionFactory extends Factory
{
    protected $model = UsrWebstoreTransaction::class;

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
            'transaction_id' => fake()->uuid(),
            'order_id' => null,
            'is_sandbox' => 0,
            'status' => WebStoreConstant::TRANSACTION_STATUS_PENDING,
            'error_code' => null,
            'item_grant_status' => null,
            'bank_status' => null,
            'adjust_status' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
