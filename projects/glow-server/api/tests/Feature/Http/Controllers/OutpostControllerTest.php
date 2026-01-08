<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Outpost\Enums\OutpostEnhancementType;
use App\Domain\Outpost\Models\UsrOutpost;
use App\Domain\Outpost\Models\UsrOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Resource\Mst\Models\MstOutpost;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancement;
use App\Domain\Resource\Mst\Models\MstOutpostEnhancementLevel;
use App\Domain\Resource\Mst\Models\MstUserLevel;
use App\Domain\User\Models\UsrUserParameter;
use App\Exceptions\HttpStatusCode;

class OutpostControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/outpost/';

    public function testOutpostEnhance_レコードない()
    {
        $outpostId = 'outpost_1';
        $enhancementId1 = 'enhancement_1';
        $enhancementId2 = 'enhancement_2';
        MstOutpost::factory()->create([
            'id' => $outpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->count(2)
            ->sequence(
                ['id' => $enhancementId1, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value],
                ['id' => $enhancementId2, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value],
            )->create();
        MstOutpostEnhancementLevel::factory()->count(2)
            ->sequence(
                ['id' => 'level_1', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
                ['id' => 'level_2', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
            )->create();

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 1000,
            'exp' => 100,
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        $response = $this->sendRequest('enhance', [
            'mstOutpostEnhancementId' => $enhancementId1,
            'level' => 2,
        ]);
        $response = $response->json();

        $this->assertEquals(1, $response['beforeLevel']);
        $this->assertEquals(2, $response['afterLevel']);
        $this->assertEquals(1000 - 200, $response['usrParameter']['coin']);
    }

    public function testOutpostEnhance_レコードある()
    {
        $outpostId = 'outpost_1';
        $enhancementId1 = 'enhancement_1';
        $enhancementId2 = 'enhancement_2';
        MstOutpost::factory()->create([
            'id' => $outpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->count(2)
            ->sequence(
                ['id' => $enhancementId1, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value],
                ['id' => $enhancementId2, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value],
            )->create();
        MstOutpostEnhancementLevel::factory()->createMany([
            ['mst_outpost_enhancement_id' => $enhancementId1, 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
            ['mst_outpost_enhancement_id' => $enhancementId1, 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
            ['mst_outpost_enhancement_id' => $enhancementId1, 'level' => 3, 'cost_coin' => 300, 'enhancement_value' => 300],
        ]);

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        $usrUserParameter = UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 1000,
            'exp' => 100,
        ]);
        UsrOutpostEnhancement::factory()->createMany([
            ['usr_user_id' => $usrUser->getId(), 'mst_outpost_id' => $outpostId, 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 1],
        ]);
        MstUserLevel::factory()->create([
            'level' => $usrUserParameter->getLevel(),
            'stamina' => 10,
        ]);

        $response = $this->sendRequest('enhance', [
            'mstOutpostEnhancementId' => $enhancementId1,
            'level' => 3,
        ]);
        $response->assertStatus(HttpStatusCode::SUCCESS);
        $response = $response->json();

        $this->assertEquals(1, $response['beforeLevel']);
        $this->assertEquals(3, $response['afterLevel']);
        $this->assertEquals(1000 - 500, $response['usrParameter']['coin']);
    }

    public function testOutpostEnhanceError_到達済みレベル()
    {
        $outpostId = 'outpost_1';
        $enhancementId1 = 'enhancement_1';
        $enhancementId2 = 'enhancement_2';
        MstOutpost::factory()->create([
            'id' => $outpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->count(2)
            ->sequence(
                ['id' => $enhancementId1, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value],
                ['id' => $enhancementId2, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value],
            )->create();
        MstOutpostEnhancementLevel::factory()->count(2)
            ->sequence(
                ['id' => 'level_1', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
                ['id' => 'level_2', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
            )->create();

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 1000,
            'exp' => 100,
        ]);
        UsrOutpostEnhancement::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'mst_outpost_id' => $outpostId,
            'mst_outpost_enhancement_id' => $enhancementId1,
            'level' => 2,
        ]);

        $response = $this->sendRequest('enhance', [
            'mstOutpostEnhancementId' => $enhancementId1,
            'level' => 2,
        ]);
        $this->assertEquals(ErrorCode::INVALID_PARAMETER, $response->json()['errorCode']);
    }

    public function testOutpostEnhance_存在しないレベル()
    {
        $outpostId = 'outpost_1';
        $enhancementId1 = 'enhancement_1';
        $enhancementId2 = 'enhancement_2';
        MstOutpost::factory()->create([
            'id' => $outpostId,
            'start_at' => '2021-01-01 00:00:00',
            'end_at' => '2037-01-01 00:00:00',
        ]);
        MstOutpostEnhancement::factory()->count(2)
            ->sequence(
                ['id' => $enhancementId1, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[0]->value],
                ['id' => $enhancementId2, 'mst_outpost_id' => $outpostId, 'outpost_enhancement_type' => OutpostEnhancementType::cases()[1]->value],
            )->create();
        MstOutpostEnhancementLevel::factory()->count(2)
            ->sequence(
                ['id' => 'level_1', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 1, 'cost_coin' => 0, 'enhancement_value' => 100],
                ['id' => 'level_2', 'mst_outpost_enhancement_id' => $enhancementId1, 'level' => 2, 'cost_coin' => 200, 'enhancement_value' => 200],
            )->create();

        $usrUser = $this->createUsrUser();
        $this->createDiamond($usrUser->getId(), freeDiamond: 0);
        UsrUserParameter::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'level' => 1,
            'coin' => 1000,
            'exp' => 100,
        ]);

        $response = $this->sendRequest('enhance', [
            'mstOutpostEnhancementId' => $enhancementId1,
            'level' => 3,
        ]);
        $this->assertEquals(ErrorCode::MST_NOT_FOUND, $response->json()['errorCode']);
    }

    public function test_changeArtwork_リクエストを送ると200OKが返ることを確認する()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $beforeArtworkId = 'artwork_1';
        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_2';
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
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $requestData = [
            'mstOutpostId' => $mstOutpostId,
            'mstArtworkId' => $mstArtworkId,
        ];
        $response = $this->sendRequest('change_artwork', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンスを確認
        $response = $response->json();
        $this->assertEquals($mstArtworkId, $response['usrOutpost']['mstArtworkId']);
    }

    public function test_changeArtwork_ゲート表紙を外す場合()
    {
        // Setup
        $usrUserId = $this->createUsrUser()->getId();
        $mstOutpostId = 'outpost_1';
        $mstArtworkId = 'artwork_1';
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
        ]);
        UsrArtwork::factory()->createMany([
            [
                'usr_user_id' => $usrUserId,
                'mst_artwork_id' => $mstArtworkId,
            ],
        ]);

        // Exercise
        $requestData = [
            'mstOutpostId' => $mstOutpostId,
            'mstArtworkId' => '',
        ];
        $response = $this->sendRequest('change_artwork', $requestData);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        // レスポンスを確認
        $response = $response->json();
        $this->assertNull($response['usrOutpost']['mstArtworkId']);
    }
}
