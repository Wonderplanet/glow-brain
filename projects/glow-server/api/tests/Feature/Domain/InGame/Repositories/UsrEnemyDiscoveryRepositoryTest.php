<?php

namespace Tests\Feature\Domain\InGame\Repositories;

use App\Domain\InGame\Models\UsrEnemyDiscovery;
use App\Domain\InGame\Repositories\UsrEnemyDiscoveryRepository;
use Tests\TestCase;

class UsrEnemyDiscoveryRepositoryTest extends TestCase
{
    private UsrEnemyDiscoveryRepository $usrEnemyDiscoveryRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->usrEnemyDiscoveryRepository = app(UsrEnemyDiscoveryRepository::class);
    }

    public function test_getByMstEnemyCharacterIds_キャッシュから取得できる()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $mstEnemyCharacterIds = collect([
            'enemy99', 'enemy1', 'enemy5',
        ]);

        $dbModels = UsrEnemyDiscovery::factory()->createMany([
            // 取得対象
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy1',],
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy5',],
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy99',],
            // 取得対象外
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy100',],
            ['usr_user_id' => $usrUserId, 'mst_enemy_character_id' => 'enemy200',],
        ]);

        // キャッシュにモデルをセット
        // キャッシュから取ったと分かるように確認用列(is_cache_for_test)を一時的に追加する
        $cacheModels = collect();
        foreach ($dbModels as $dbModel) {
            $model = clone $dbModel;
            $model->is_cache_for_test = true;
            $cacheModels->push($model);
        }
        $this->usrEnemyDiscoveryRepository->syncModels($cacheModels);

        // Exercise
        $result = $this->usrEnemyDiscoveryRepository->getByMstEnemyCharacterIds(
            $usrUserId, $mstEnemyCharacterIds,
        );

        // Verify
        $this->assertEqualsCanonicalizing(
            $mstEnemyCharacterIds->toArray(),
            $result->pluck('mst_enemy_character_id')->toArray(),
        );
        $result->each(function (UsrEnemyDiscovery $model) {
            $this->assertTrue($model->is_cache_for_test);
        });
    }
}
