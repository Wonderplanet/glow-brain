<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use Tests\TestCase;
use WonderPlanet\Domain\Admin\Trait\DatabaseTransactionTrait;
use WonderPlanet\Domain\Common\Utils\DBUtility;
use WonderPlanet\Tests\Support\AdmUser;
use WonderPlanet\Tests\Support\UsrUser;

class DatabaseTransactionTraitTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @test
     */
    public function transaction_AdmUser_正常にコミットされる(): void
    {
        /** @var AdmUser $beforeAdmUser */
        $beforeAdmUser = AdmUser::query()->create([
            'id' => 1,
            'name' => fake()->name(),
            'email' => 'test@wonderpla.net',
            'password' => 'admintest'
        ]);

        $tableName = $beforeAdmUser->getTable();

        // Exercise
        $this->transaction(function () use ($beforeAdmUser) {
            AdmUser::query()
                ->where('id', $beforeAdmUser->id)
                ->update(['name' => 'test']);
        });

        // Verify
        $this->assertDatabaseHas(
            $tableName,
            [
                'id' => $beforeAdmUser->id,
                'name' => 'test'
            ]
        );
    }

    /**
     * @test
     */
    public function transaction_AdmUser_ロールバックされる(): void
    {
        /** @var AdmUser $beforeAdmUser */
        $beforeAdmUser = AdmUser::query()->create([
            'id' => 1,
            'name' => fake()->name(),
            'email' => 'test@wonderpla.net',
            'password' => 'admintest'
        ]);

        $tableName = $beforeAdmUser->getTable();

        // Exercise
        try {
            $this->transaction(function () use ($beforeAdmUser) {
                AdmUser::query()
                    ->where('id', $beforeAdmUser->id)
                    ->update(['name' => 'test']);
                throw new \Exception();
            });
        } catch (\Exception $e) {
            // Verify
            //   変更がないことを確認
            $this->assertDatabaseHas(
                $tableName,
                [
                    'id' => $beforeAdmUser->id,
                    'name' => $beforeAdmUser->name
                ]
            );
            return;
        }
        $this->fail('例外が発生しませんでした');
    }

    /**
     * @test
     */
    public function transaction_UsrUser_正常にコミットされる(): void
    {
        // Setup
        /** @var UsrUser $beforeUsrUser */
        $beforeUsrUser = UsrUser::create();
        $beforeUsrUser->tutorial_status = 1;
        $beforeUsrUser->save();

        $tableName = $beforeUsrUser->getTable();

        // Exercise
        $this->transaction(function () use ($beforeUsrUser) {
            $usrUser = UsrUser::query()
                ->find($beforeUsrUser->id);
            $usrUser->tutorial_status = 2;
            $usrUser->save();
        }, [DBUtility::getUsrConnName()]);

        // Verify
        $this->assertDatabaseHas(
            $tableName,
            [
                'id' => $beforeUsrUser->id,
                'tutorial_status' => 2,
            ],
            DBUtility::getUsrConnName()
        );
    }

    /**
     * @test
     */
    public function transaction_UsrUser_ロールバックされる(): void
    {
        /** @var UsrUser $beforeUsrUser */
        $beforeUsrUser = UsrUser::create();
        $beforeUsrUser->tutorial_status = 1;
        $beforeUsrUser->save();

        $tableName = $beforeUsrUser->getTable();

        // Exercise
        try {
            $this->transaction(function () use ($beforeUsrUser) {
                $usrUser = UsrUser::query()
                    ->find($beforeUsrUser->id);
                $usrUser->tutorial_status = 2;
                $usrUser->save();

                throw new \Exception();
            }, [DBUtility::getUsrConnName()]);
        } catch (\Exception $e) {
            // Verify
            //   変更がないことを確認
            $this->assertDatabaseHas(
                $tableName,
                [
                    'id' => $beforeUsrUser->id,
                    'tutorial_status' => 1,
                ],
                DBUtility::getUsrConnName()
            );
            return;
        }

        $this->fail('例外が発生しませんでした');
    }
}
