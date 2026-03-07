<?php

declare(strict_types=1);

namespace Feature\Domain\Campaign\Repositories;

use App\Domain\Campaign\Enums\CampaignTargetIdType;
use App\Domain\Campaign\Enums\CampaignType;
use App\Domain\Resource\Mst\Models\MstQuest;
use App\Domain\Resource\Mst\Models\OprCampaign;
use App\Domain\Resource\Mst\Repositories\OprCampaignRepository;
use App\Domain\Stage\Enums\QuestType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OprCampaignRepositoryTest extends TestCase
{
    private OprCampaignRepository $oprCampaignRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->oprCampaignRepository = app(OprCampaignRepository::class);
    }

    public static function params_testGetActiveOprCampaignsByMstQuest_条件に一致するキャンペーン情報が取得できる()
    {
        return [
            '対象とクエストID指定(Normal難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest1',
                    'quest_type' => QuestType::NORMAL->value,
                    'difficulty' => 'Normal',
                ],
                'expectedOprCampaignIds' => collect(['campaign1', 'campaign7']),
            ],
            '対象と難易度とクエストID指定(Hard難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest2',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Hard',
                ],
                'expectedOprCampaignIds' => collect(['campaign2']),
            ],
            '対象と難易度とクエストID指定(Extra難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest3',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Extra',
                ],
                'expectedOprCampaignIds' => collect(['campaign3']),
            ],
            '対象と難易度とシリーズID指定(Normal難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest4',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Normal',
                    'mst_series_id' => 'spy',
                ],
                'expectedOprCampaignIds' => collect(['campaign4']),
            ],
            '対象と難易度とシリーズID指定(Hard難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest5',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Hard',
                    'mst_series_id' => 'jigoku',
                ],
                'expectedOprCampaignIds' => collect(['campaign5']),
            ],
            '対象と難易度とシリーズID指定(Extra難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest6',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Extra',
                    'mst_series_id' => 'kaguya',
                ],
                'expectedOprCampaignIds' => collect(['campaign6']),
            ],
            '複数キャンペーンに該当する(同じ難易度)' => [
                'mstQuestParam' => [
                    'id' => 'quest3',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Extra',
                    'mst_series_id' => 'kaguya',
                ],
                'expectedOprCampaignIds' => collect(['campaign3', 'campaign6']),
            ],
            '難易度が異なる場合でもtarget_id未設定のキャンペーンは適用される' => [
                'mstQuestParam' => [
                    'id' => 'quest1',
                    'quest_type' => QuestType::NORMAL->value,
                    'difficulty' => 'Hard',
                ],
                'expectedOprCampaignIds' => collect(['campaign9']),
            ],
            'クエストIDが異なる場合でもtarget_id未設定のキャンペーンは適用される' => [
                'mstQuestParam' => [
                    'id' => 'quest999',
                    'quest_type' => QuestType::NORMAL->value,
                    'difficulty' => 'Normal',
                ],
                'expectedOprCampaignIds' => collect(['campaign7']),
            ],
            'シリーズIDが異なるため該当しない' => [
                'mstQuestParam' => [
                    'id' => 'quest4',
                    'quest_type' => QuestType::EVENT->value,
                    'difficulty' => 'Normal',
                    'mst_series_id' => 'other',
                ],
                'expectedOprCampaignIds' => collect([]),
            ],
            'target_id_typeがSERIESでtarget_idが空文字の場合は全体に適用される' => [
                'mstQuestParam' => [
                    'id' => 'quest8',
                    'quest_type' => QuestType::ENHANCE->value,
                    'difficulty' => 'Normal',
                    'mst_series_id' => 'any_series',
                ],
                'expectedOprCampaignIds' => collect(['campaign8']),
            ],
        ];
    }

    /**
     * @dataProvider params_testGetActiveOprCampaignsByMstQuest_条件に一致するキャンペーン情報が取得できる
     */
    public function testGetActiveOprCampaignsByMstQuest_条件に一致するキャンペーン情報が取得できる(
        array $mstQuestParam,
        Collection $expectedOprCampaignIds
    ) {
        // Setup
        $now = CarbonImmutable::now();
        OprCampaign::factory()->createMany([
            // Normal難易度 + クエストID指定
            [
                'id' => 'campaign1',
                'campaign_type' => CampaignType::STAMINA->value,
                'target_type' => 'NormalQuest',
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest1',
            ],
            // Hard難易度 + クエストID指定
            [
                'id' => 'campaign2',
                'campaign_type' => CampaignType::EXP->value,
                'target_type' => 'EventQuest',
                'difficulty' => 'Hard',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest2',
            ],
            // Extra難易度 + クエストID指定
            [
                'id' => 'campaign3',
                'campaign_type' => CampaignType::COIN_DROP->value,
                'target_type' => 'EventQuest',
                'difficulty' => 'Extra',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => 'quest3',
            ],
            // Normal難易度 + シリーズID指定
            [
                'id' => 'campaign4',
                'campaign_type' => CampaignType::ITEM_DROP->value,
                'target_type' => 'EventQuest',
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::SERIES->value,
                'target_id' => 'spy',
            ],
            // Hard難易度 + シリーズID指定
            [
                'id' => 'campaign5',
                'campaign_type' => CampaignType::ARTWORK_FRAGMENT->value,
                'target_type' => 'EventQuest',
                'difficulty' => 'Hard',
                'target_id_type' => CampaignTargetIdType::SERIES->value,
                'target_id' => 'jigoku',
            ],
            // Extra難易度 + シリーズID指定
            [
                'id' => 'campaign6',
                'campaign_type' => CampaignType::CHALLENGE_COUNT->value,
                'target_type' => 'EventQuest',
                'difficulty' => 'Extra',
                'target_id_type' => CampaignTargetIdType::SERIES->value,
                'target_id' => 'kaguya',
            ],
            // Normal難易度 + クエストID指定だがtarget_idがnull
            [
                'id' => 'campaign7',
                'campaign_type' => CampaignType::EXP->value,
                'target_type' => 'NormalQuest',
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => null,
            ],
            // Normal難易度 + シリーズID指定だがtarget_idが空文字
            [
                'id' => 'campaign8',
                'campaign_type' => CampaignType::ITEM_DROP->value,
                'target_type' => 'EnhanceQuest',
                'difficulty' => 'Normal',
                'target_id_type' => CampaignTargetIdType::SERIES->value,
                'target_id' => '',
            ],
            // Hard難易度 + クエストID指定だがtarget_idがnull
            [
                'id' => 'campaign9',
                'campaign_type' => CampaignType::EXP->value,
                'target_type' => 'NormalQuest',
                'difficulty' => 'Hard',
                'target_id_type' => CampaignTargetIdType::QUEST->value,
                'target_id' => null,
            ],
        ]);
        $mstQuest = MstQuest::factory()->create($mstQuestParam)->toEntity();

        // Exercise
        $actual = $this->oprCampaignRepository->getActivesByMstQuest($now, $mstQuest);

        $this->assertEquals($expectedOprCampaignIds, $actual->map(fn($entity) => $entity->getId())->values());
    }
}
