<?php

declare(strict_types=1);

namespace Tests\Unit\Common\Traits;

use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\User\Models\UsrUser;
use Tests\TestCase;

class UseCaseTraitTest extends TestCase
{
    use UseCaseTrait;

    /**
     * @test
     */
    public function transaction_正常にコミットされる()
    {
        // Setup
        //  更新用のデータとして適当なユーザーデータを作成
        $beforeUser = UsrUser::factory()->create();
        $tableName = $beforeUser->getTable();

        // Exercise
        $this->applyUserTransactionChanges(function () {
            UsrUser::query()->update(['tutorial_status' => 2]);
        });

        // Verify
        $this->assertDatabaseHas(
            $tableName,
            [
                'id' => $beforeUser->getId(),
                'tutorial_status' => 2,
            ]
        );
    }

    /**
     * @test
     */
    public function transaction_ロールバックされる()
    {
        // Setup
        //  更新用のデータとして適当なユーザーデータを作成
        $beforeUser = UsrUser::factory()->create(
            [
                'tutorial_status' => 'before',
            ]
        );
        $tableName = $beforeUser->getTable();

        // Exercise
        try {
            $this->applyUserTransactionChanges(function () {
                UsrUser::query()->update(['tutorial_status' => 'after']);
                throw new \Exception();
            });
        } catch (\Exception $e) {
            // Verify
            //   変更がないことを確認
            $this->assertDatabaseHas(
                $tableName,
                [
                    'id' => $beforeUser->getId(),
                    'tutorial_status' => 'before',
                ]
            );
            return;
        }

        $this->fail('例外が発生しませんでした');
    }
}
