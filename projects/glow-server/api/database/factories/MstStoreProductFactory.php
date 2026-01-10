<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Resource\Mst\Models\MstStoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Resource\Mst\Models\MstStoreProduct>
 */
class MstStoreProductFactory extends Factory
{
    protected $model = MstStoreProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => '',
            'release_key' => 1,
            'product_id_ios' => '',
            'product_id_android' => '',
        ];
    }

    /**
     * 疎通確認モック用のデータを作成
     *
     * @return array
     */
    public function createMockData(): array
    {
        $productIds = [
            'edmo_pack_160_1_framework',
            'edmo_pack_480_1_framework',
            'edmo_pack_1000_1_framework',
            'edmo_pack_3000_1_framework',
            'edmo_pack_9800_1_framework',
        ];
        $models = [];
        foreach ($productIds as $productId) {
            $models[] = $this->create(
                [
                    'id' => $productId,
                    'product_id_ios' => "ios_{$productId}",
                    'product_id_android' => "android_{$productId}",
                ]);
        }
        return $models;
    }
}
