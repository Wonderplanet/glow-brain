<?php

namespace Tests\Feature\Domain\Outpost\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use Tests\Support\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\UseCases\OutpostChangeArtworkUseCase;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstOutpost;
use Tests\TestCase;

class OutpostChangeArtworkUseCaseTest extends TestCase
{
    private OutpostChangeArtworkUseCase $usecase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usecase = app(OutpostChangeArtworkUseCase::class);
    }

    public function test_exec_ゲート表紙が変更できる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);

        $beforeArtworkId = 'artwork_1';
        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_2';
        $isUsed = 1;
        MstOutpost::factory()->create([
            'id' => $mstOutpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstArtwork::factory()->createMany([
            [
                'id' => $mstArtworkId,
            ],
        ]);

        UsrOutpost::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => $mstOutpostId,
            'mst_artwork_id' => $beforeArtworkId,
            'is_used' => $isUsed,
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $result = $this->usecase->exec($currentUser, $mstOutpostId, $mstArtworkId);

        // Verify
        // レスポンスを確認
        $this->assertEquals($mstOutpostId, $result->usrOutpost->getMstOutpostId());
        $this->assertEquals($mstArtworkId, $result->usrOutpost->getMstArtworkId());
        $this->assertEquals($isUsed, $result->usrOutpost->getIsUsed());

        // DBを確認
        $usrOutpost = UsrOutpost::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstOutpostId, $usrOutpost->getMstOutpostId());
        $this->assertEquals($mstArtworkId, $usrOutpost->getMstArtworkId());
        $this->assertEquals($isUsed, $usrOutpost->getIsUsed());
    }

    public function test_exec_ゲート表紙を外せる()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_2';
        $isUsed = 1;
        MstOutpost::factory()->create([
            'id' => $mstOutpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstArtwork::factory()->createMany([
            [
                'id' => $mstArtworkId,
            ],
        ]);

        UsrOutpost::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => $mstOutpostId,
            'mst_artwork_id' => $mstArtworkId,
            'is_used' => $isUsed,
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $result = $this->usecase->exec($currentUser, $mstOutpostId, '');

        // Verify
        // レスポンスを確認
        $this->assertEquals($mstOutpostId, $result->usrOutpost->getMstOutpostId());
        $this->assertNull($result->usrOutpost->getMstArtworkId());
        $this->assertEquals($isUsed, $result->usrOutpost->getIsUsed());

        // DBを確認
        $usrOutpost = UsrOutpost::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstOutpostId, $usrOutpost->getMstOutpostId());
        $this->assertNull($usrOutpost->getMstArtworkId());
        $this->assertEquals($isUsed, $usrOutpost->getIsUsed());
    }

    public function test_exec_ゲートを持っていない場合()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_1';
        $isUsed = 1;
        MstOutpost::factory()->create([
            'id' => $mstOutpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstArtwork::factory()->createMany([
            [
                'id' => $mstArtworkId,
            ],
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::OUTPOST_NOT_OWNED);

        $result = $this->usecase->exec($currentUser, $mstOutpostId, $mstArtworkId);
    }

    public function test_exec_原画を持っていない場合()
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();
        $currentUser = new CurrentUser($usrUserId);

        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_1';
        $isUsed = 1;
        MstOutpost::factory()->create([
            'id' => $mstOutpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstArtwork::factory()->createMany([
            [
                'id' => $mstArtworkId,
            ],
        ]);
        UsrOutpost::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_outpost_id' => $mstOutpostId,
            'is_used' => $isUsed,
        ]);

        // Exercise
        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::ARTWORK_NOT_OWNED);

        $result = $this->usecase->exec($currentUser, $mstOutpostId, $mstArtworkId);
    }
}
