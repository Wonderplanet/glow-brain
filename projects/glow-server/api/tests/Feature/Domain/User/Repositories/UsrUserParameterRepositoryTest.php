<?php

namespace Tests\Feature\Domain\User\Repositories;

use App\Domain\User\Models\UsrUserParameter;
use App\Domain\User\Repositories\UsrUserParameterRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsrUserParameterRepositoryTest extends TestCase
{
    private UsrUserParameterRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UsrUserParameterRepository::class);
    }

    public function test_saveModels_発行されるクエリの確認()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();

        $model = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUserId,
            'stamina' => 10,
        ]);
        $model->stamina = 15;

        // 実行されるクエリを記録する
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        // Exercise
        $this->execPrivateMethod($this->repository, 'saveModels', [collect([$model])]);

        // Verify
        // 実行クエリの確認
        $this->assertCount(1, $queries);
        $this->assertEquals(
            "update `usr_user_parameters` set `stamina` = ?, `usr_user_parameters`.`updated_at` = ? where `usr_user_id` = ?",
            $queries[0]
        );

        $models = UsrUserParameter::all();
        $this->assertCount(1, $models);
        $this->assertEquals($usrUserId, $models[0]->usr_user_id);
        $this->assertEquals(15, $models[0]->stamina);
    }
}
