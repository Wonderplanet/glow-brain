<?php

namespace Feature\Domain\Outpost\Services;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Outpost\Services\UserOutpostService;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use Tests\TestCase;

class UserOutpostServiceTest extends TestCase
{
    private UserOutpostService $userOutpostService;

    public function setUp(): void
    {
        parent::setUp();

        $this->userOutpostService = $this->app->make(UserOutpostService::class);
    }

    public function testExistRecord()
    {
        $userId = $this->usrModelManager->getUsrUserId();
        $mstOutpostEnhancementEntity = MstOutpostEnhancement::factory()->create()->toEntity();
        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $userId,
            'mst_outpost_id' => $mstOutpostEnhancementEntity->getMstOutpostId(),
            'mst_outpost_enhancement_id' => $mstOutpostEnhancementEntity->getId(),
            'level' => 3,
        ]);

        $usrOutpostEnhance = $this->userOutpostService->findUsrOutpostEnhancementByEnhancementId(
            $userId,
            $mstOutpostEnhancementEntity,
        );

        $this->assertEquals($userId, $usrOutpostEnhance->getUsrUserId());
        $this->assertEquals($mstOutpostEnhancementEntity->getMstOutpostId(), $usrOutpostEnhance->getMstOutpostId());
        $this->assertEquals($mstOutpostEnhancementEntity->getId(), $usrOutpostEnhance->getMstOutpostEnhancementId());
        $this->assertEquals(3, $usrOutpostEnhance->getLevel());
    }

    public function testNoRecord()
    {
        $userId = $this->usrModelManager->getUsrUserId();
        $mstOutpostEnhancementEntity = MstOutpostEnhancement::factory()->create()->toEntity();

        $usrOutpostEnhance = $this->userOutpostService->findUsrOutpostEnhancementByEnhancementId(
            $userId,
            $mstOutpostEnhancementEntity,
        );

        $this->assertEquals($userId, $usrOutpostEnhance->getUsrUserId());
        $this->assertEquals($mstOutpostEnhancementEntity->getMstOutpostId(), $usrOutpostEnhance->getMstOutpostId());
        $this->assertEquals($mstOutpostEnhancementEntity->getId(), $usrOutpostEnhance->getMstOutpostEnhancementId());
        $this->assertEquals(1, $usrOutpostEnhance->getLevel());
    }

    /**
     * @dataProvider params_setMstArtworkId_原画IDをセットできる
     */
    public function test_setArtwork(string $expectMstArtworkId)
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_1';
        $beforeMstArtworkId = 'artwork_2';

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
            'mst_artwork_id' => $beforeMstArtworkId,
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $this->userOutpostService->setArtwork($usrUserId, $mstOutpostId, $expectMstArtworkId);
        $this->saveAll();

        // Verify
        $usrOutpost = UsrOutpost::query()->where('usr_user_id', $usrUserId)->first();
        $this->assertEquals($mstOutpostId, $usrOutpost->getMstOutpostId());
        $this->assertEquals($expectMstArtworkId, $usrOutpost->getMstArtworkId());
    }

    public static function params_setMstArtworkId_原画IDをセットできる(): array
    {
        return [
            '正常系' => [
                'expectMstArtworkId' => 'artwork_1',
            ],
            '外す場合' => [
                'expectMstArtworkId' => '',
            ],
        ];
    }
}
