<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Encyclopedia\Models\UsrArtwork;
use App\Domain\Party\Constants\PartyConstant;
use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Resource\Mst\Models\MstArtwork;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Exceptions\HttpStatusCode;

class PartyControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/party/';

    public function test_save_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
    {
        // Setup
        $usrUser = $this->createUsrUser();

        $usrUnitIds = collect(['unit1', 'unit2', 'unit3']);
        $usrUnitIds->map(function ($usrUnitId) use ($usrUser) {
            return UsrUnit::factory()->create([
                'id' => $usrUnitId,
                'usr_user_id' => $usrUser->getId(),
            ]);
        });
        UsrParty::factory()->create([
            'usr_user_id' => $usrUser->getId(),
            'party_no' => 1,
            'party_name' => 'test',
            'usr_unit_id_1' => $usrUnitIds[0],
        ]);

        // Exercise
        $requestParty = ['partyNo' => 1, 'partyName' => 'TestParty', 'units' => $usrUnitIds->toArray()];
        $response = $this->sendRequest('save', ['parties' => [$requestParty]]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $usrParties = $response->json()['usrParties'];

        $this->assertCount(1, $usrParties);

        $usrParty = $usrParties[0];
        $this->assertEquals($requestParty['partyNo'], $usrParty['partyNo']);
        $this->assertEquals($requestParty['partyName'], $usrParty['partyName']);

        // 1~3枠目のユニットが設定され4~10枠目がnullであることを確認
        foreach ($usrUnitIds->pad(10, null) as $i => $usrUnitId) {
            $columnName = "usrUnitId" . ($i + 1);
            $this->assertEquals($usrUnitId, $usrParty[$columnName]);
        }
    }

    public function test_artworkSave_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する(): void
    {
        // Setup
        $usrUser = $this->createUsrUser();
        $usrUserId = $usrUser->getId();

        $mstArtworkIds = ['artwork_1', 'artwork_2', 'artwork_3'];
        foreach ($mstArtworkIds as $mstArtworkId) {
            $this->createArtwork($usrUserId, $mstArtworkId);
        }

        // Exercise
        $response = $this->sendRequest('artwork/save', ['mstArtworkIds' => $mstArtworkIds]);

        // Verify
        $response->assertStatus(HttpStatusCode::SUCCESS);

        $usrArtworkParty = $response->json()['usrArtworkParty'];

        $this->assertEquals($mstArtworkIds[0], $usrArtworkParty['mstArtworkId1']);
        $this->assertEquals($mstArtworkIds[1], $usrArtworkParty['mstArtworkId2']);
        $this->assertEquals($mstArtworkIds[2], $usrArtworkParty['mstArtworkId3']);

        // 4~10枠目がnullであることを確認
        for ($i = 4; $i <= PartyConstant::MAX_ARTWORK_COUNT_IN_PARTY; $i++) {
            $this->assertNull($usrArtworkParty["mstArtworkId{$i}"]);
        }
    }

    /**
     * テスト用に原画を作成するヘルパーメソッド
     * 原画を所持している状態を作成する
     */
    private function createArtwork(string $usrUserId, string $mstArtworkId): void
    {
        MstArtwork::factory()->create(['id' => $mstArtworkId]);

        UsrArtwork::factory()->create([
            'usr_user_id' => $usrUserId,
            'mst_artwork_id' => $mstArtworkId,
        ]);
    }
}
