<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Unit\Repositories;

use App\Domain\Unit\Models\UsrUnitSummary;
use App\Domain\Unit\Repositories\UsrUnitSummaryRepository;
use Tests\TestCase;

class UsrUnitSummaryRepositoryTest extends TestCase
{
    private UsrUnitSummaryRepository $usrUnitSummaryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usrUnitSummaryRepository = $this->app->make(UsrUnitSummaryRepository::class);
    }

    public function test_syncModel_同じusr_user_idのモデルが複数あっても_キャッシュには1つだけ保存されることを確認(): void
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        // 同じusr_user_idだが異なるidを持つ2つのインスタンスを作成
        $model1 = new UsrUnitSummary();
        $model1->id = $model1->newUniqueId();
        $model1->usr_user_id = $usrUserId;
        $model1->grade_level_total_count = 10;

        $model2 = new UsrUnitSummary();
        $model2->id = $model2->newUniqueId();
        $model2->usr_user_id = $usrUserId;
        $model2->grade_level_total_count = 20;

        // idが異なることを確認
        $this->assertNotEquals($model1->id, $model2->id);
        // usr_user_idは同じことを確認
        $this->assertEquals($model1->usr_user_id, $model2->usr_user_id);

        // Exercise: 1つ目のモデルをsyncModel
        $this->usrUnitSummaryRepository->syncModel($model1);

        // 1つ目のモデルがキャッシュに保存されていることを確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[UsrUnitSummaryRepository::class] ?? [];
        $this->assertCount(1, $cachedModels, '1つ目のsyncModel後、キャッシュには1つのモデルが保存されている');

        // Exercise: 2つ目のモデルをsyncModel
        $this->usrUnitSummaryRepository->syncModel($model2);

        // Verify: キャッシュには依然として1つのモデルしかないことを確認
        $cachedModels = $this->getUsrModelManagerPrivateVariable('models')[UsrUnitSummaryRepository::class] ?? [];
        $this->assertCount(1, $cachedModels, '2つ目のsyncModel後も、キャッシュには1つのモデルのみ保存されている');

        // 最後にsyncModelされたモデル（model2）がキャッシュに保存されていることを確認
        $cachedModel = array_values($cachedModels)[0];
        $this->assertEquals($model2->id, $cachedModel->id, '最後にsyncModelされたモデルがキャッシュに保存されている');
        $this->assertEquals(20, $cachedModel->grade_level_total_count, '最後のモデルのデータが保存されている');
    }
}
