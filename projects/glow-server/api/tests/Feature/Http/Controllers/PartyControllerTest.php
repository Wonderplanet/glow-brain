<?php

namespace Tests\Feature\Http\Controllers;

use App\Domain\Party\Models\Eloquent\UsrParty;
use App\Domain\Unit\Models\Eloquent\UsrUnit;
use App\Exceptions\HttpStatusCode;

class PartyControllerTest extends BaseControllerTestCase
{
    protected string $baseUrl = '/api/party/';

    public function testSave_リクエストを送ると200OKが返り想定通りのレスポンスが返ることを確認する()
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
}
