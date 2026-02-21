<?php

namespace Tests\Feature\Domain\DebugCommand\UseCases;

use App\Domain\DebugCommand\UseCases\Commands\GrantStaminaMaxUseCase;
use App\Domain\DebugCommand\UseCases\DebugCommandListUseCase;
use Tests\TestCase;

class DebugCommandListUseCaseTest extends TestCase
{
    /**
     * @test
     */
    public function デバッグコマンドリストを取得する()
    {
        // Setup
        $useCase = new DebugCommandListUseCase();
        $expects = [
            "commands" => [
                [
                    'command' => 'AddAllEmblem',
                    'name' => '全エンブレム付与',
                    'description' => 'マスターに登録されてる未所持のエンブレムを付与します',
                ],
                [
                    'command' => 'AddAllUnit',
                    'name' => '全ユニット付与',
                    'description' => 'マスターに登録されてる未所持のユニットを付与します',
                ],
                [
                    'command' => 'AddUserExp',
                    'name' => 'プレイヤー経験値の付与',
                    'description' => '次のレベルに上がる直前までの経験値を付与します',
                ],
                [
                    'command' => 'DeleteAllArtwork',
                    'name' => '所持原画の一斉削除',
                    'description' => '所持原画を一斉削除します。',
                ],
                [
                    'command' => 'DeleteAllEmblem',
                    'name' => '所持エンブレムの一斉削除',
                    'description' => '所持しているエンブレムを一斉削除します。',
                ],
                [
                    'command' => 'GrantUserItemMax',
                    'name' => 'ユーザーの所持アイテム付与＆MAX',
                    'description' => 'ユーザーの所持アイテム付与＆MAXにします',
                ],
                [
                    'command' => 'DeleteAllItem',
                    'name' => '所持アイテムの一斉削除',
                    'description' => '所持しているアイテムを一斉削除します。',
                ],
                [
                    'command' => 'GrantUserUnitMax',
                    'name' => 'ユーザーの所持ユニット全付与＆MAX',
                    'description' => 'ユーザーの所持ユニットを全付与＆MAXにします',
                ],
                [
                    'command' => 'GrantUserCurrencyMax',
                    'name' => 'ユーザーの所持コイン、無償プリズムMAX',
                    'description' => 'ユーザーの所持コイン、無償プリズムをMAXにします',
                ],
                [
                    'command' => 'DeleteAllGacha',
                    'name' => 'ガシャの一斉削除',
                    'description' => 'ガシャを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMessage',
                    'name' => 'メールBOXの一斉削除',
                    'description' => 'メールBOXを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllAdventBattle',
                    'name' => '降臨バトル情報の一斉削除',
                    'description' => '降臨バトル情報を一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionAchivement',
                    'name' => 'アチーブメントミッションの一斉削除',
                    'description' => 'アチーブメントミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionBeginner',
                    'name' => '初心者ミッションの一斉削除',
                    'description' => '初心者ミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionDaily',
                    'name' => 'デイリーミッションの一斉削除',
                    'description' => 'デイリーミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionEventDaily',
                    'name' => 'イベントデイリーミッションの一斉削除',
                    'description' => 'イベントデイリーミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionEvent',
                    'name' => 'イベントミッションの一斉削除',
                    'description' => 'イベントミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionWeekly',
                    'name' => 'ウィークリーミッションの一斉削除',
                    'description' => 'ウィークリーミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllMissionLimitedTerm',
                    'name' => '期間限定ミッションの一斉削除',
                    'description' => '期間限定ミッションを一斉削除します',
                ],
                [
                    'command' => 'DeleteAllUnit',
                    'name' => '所持ユニットの一斉削除',
                    'description' => '所持ユニットを一斉削除します',
                ],
                [
                    'command' => 'DeleteOutPost',
                    'name' => '所持ゲート強化項目の初期化',
                    'description' => '所持ゲート強化項目を初期化します',
                ],
                [
                    'command' => 'GrantUserOutpostMax',
                    'name' => 'ユーザーの所持のゲートレベルMAX',
                    'description' => 'ユーザーの所持ゲートレベルをMAXにします',
                ],
                [
                    'command' => 'DeleteStage',
                    'name' => '解放済みステージの一斉解除',
                    'description' => '解放済みステージの一斉解除（解放したステージデータを削除）します。',
                ],
                [
                    'command' => 'GrantStaminaMax',
                    'name' => 'スタミナ回復',
                    'description' => 'スタミナを' . GrantStaminaMaxUseCase::RECOVERY_STAMINA . '回復します',
                ],
                [
                    'command' => 'InitAllUnitStatus',
                    'name' => '所持ユニットのステータス初期化',
                    'description' => '所持ユニットのステータスを初期化します',
                ],
                [
                    'command' => 'InitEncyclopediaRank',
                    'name' => '図鑑ランクの初期化',
                    'description' => '図鑑ランクを初期化します。',
                ],
                [
                    'command' => 'ResetLimitCountContents',
                    'name' => '回数制限リセット',
                    'description' => '回数制限のあるコンテンツのリセットをします。',
                ],
                [
                    'command' => 'DeleteUsrShop',
                    'name' => 'ショップアイテム、パスデータの削除',
                    'description' => 'ショップアイテム、パスデータを削除します。',
                ],
                [
                    'command' => 'ResetNameUpdateAt',
                    'name' => 'ユーザー名更新時間をリセット',
                    'description' => 'ユーザー名更新時間をリセットして、再度ユーザー名変更ができるようにします',
                ],
                [
                    'command' => 'TutorialMainPartComplete',
                    'name' => 'チュートリアルメインパート完了',
                    'description' => 'MstTutorialのtypeがMainのデータの中で、sort_orderが最大のチュートリアルコンテンツを、完了した状態へ更新します',
                ],
                [
                    'command' => 'UserServerTimeChange',
                    'name' => 'ユーザーサーバー時間変更',
                    'description' => 'ユーザーのサーバー時間を指定した日時に変更します',
                    'requiredParameters' => [
                        'year' => [
                            'type' => 'integer',
                            'min' => 1970,
                            'max' => 2037,
                            'description' => '西暦',
                        ],
                        'month' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 12,
                            'description' => '月',
                        ],
                        'day' => [
                            'type' => 'integer',
                            'min' => 1,
                            'max' => 30,
                            'description' => '日',
                        ],
                        'hour' => [
                            'type' => 'integer',
                            'min' => 0,
                            'max' => 23,
                            'description' => '時間',
                        ],
                        'minute' => [
                            'type' => 'integer',
                            'min' => 0,
                            'max' => 59,
                            'description' => '分',
                        ],
                    ],
                ],
                [
                    'command' => 'UserServerTimeReset',
                    'name' => 'ユーザーサーバー時間リセット',
                    'description' => 'ユーザーのサーバー時間設定をリセットして、通常の時間に戻します',
                ],
            ]
        ];

        // Exercise
        $result = $useCase->exec();

        // Verify
        $this->assertCount(count($expects['commands']), $result['commands']);
        $this->assertEqualsCanonicalizing($result, $expects);
    }
}
