<?php

declare(strict_types=1);

namespace App\Traits;

use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\TextColumn;
use App\Models\Mst\MstEnemyCharacter;
use App\Tables\Columns\UnitEnemyInfoColumn;

trait LogInGameBattleTrait
{
    protected function getInGameBattleColumn(array $filterKey): Column
    {
        return TextColumn::make('in_game_battle_log')
            ->label('バトルログ')
            ->getStateUsing(
                function ($record) use ($filterKey) {
                    if ($record->in_game_battle_log) {
                        return $this->inGameBattleLog($record->in_game_battle_log, $filterKey);
                    }
                    return [];
                }
            )
            ->html()
            ->searchable()
            ->sortable();
    }

    protected function getInGameBattleColumns(Collection $mstEnemyCharacters, array $filterKey): array
    {
        return [
            $this->getInGameBattleColumn($filterKey),
            UnitEnemyInfoColumn::make('discovered_enemies')
                ->label('敵キャラ情報')
                ->getStateUsing(
                    function ($record) use ($mstEnemyCharacters) {
                        if ($record->in_game_battle_log) {
                            $inGameBattleLog = json_decode($record->in_game_battle_log, true);
                            if (array_key_exists('discovered_enemies', $inGameBattleLog)) {
                                return $this->discoveredEnemies($inGameBattleLog, $mstEnemyCharacters);
                            }
                        }
                        return;
                    }
                )
                ->searchable()
                ->sortable(),
        ];
    }

    public function inGameBattleLog($inGameBattleLog, array $filterKey)
    {
        $inGameBattleLog = json_decode($inGameBattleLog, true);
        $filteredData = array_filter(
            $inGameBattleLog,
            fn($key) => !in_array($key, $filterKey),
            ARRAY_FILTER_USE_KEY
        );

        $result = implode("<br>", array_map(function ($key, $value) {
            switch ($key) {
                case 'defeat_boss_enemy_count':
                    return "強敵撃破数: $value";
                case 'defeat_enemy_count':
                    return "敵撃破数: $value";
                case 'score':
                    return "スコア: $value";
                case 'max_damage':
                    return "最大ダメージ: $value";
                case 'clear_time_ms':
                    return "クリアタイム(ミリ秒): $value";
                case 'defeat_score':
                    return "撃破スコア: $value";
            }
            }, array_keys($filteredData),
            $filteredData
        ));
        return $result;
    }

    /**
     * @param array $inGameBattleLog
     * @param Collection<MstEnemyCharacter> $mstEnemyCharacters
     */
    public function discoveredEnemies(array $inGameBattleLog, Collection $mstEnemyCharacters)
    {
        if (array_key_exists('discovered_enemies', $inGameBattleLog)) {
            $discoveredEnemies = $inGameBattleLog['discovered_enemies'];
            $data = [];
            foreach ($discoveredEnemies as $discoveredEnemie) {
                $mstEnemyCharacter = $mstEnemyCharacters->get($discoveredEnemie['mst_enemy_character_id']);
                $data[] = [
                    'id' => $mstEnemyCharacter?->id,
                    'name' => $mstEnemyCharacter?->mst_enemy_character_i18n->name,
                    'count' => $discoveredEnemie['count'],
                    'assetPath' => $mstEnemyCharacter?->makeAssetPath(),
                    'bgPath' => $mstEnemyCharacter?->makeBgPath(),
                ];

            }

            return $data;
        }
        return;
    }

}
