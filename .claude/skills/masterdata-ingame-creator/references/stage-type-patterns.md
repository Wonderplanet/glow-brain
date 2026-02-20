# ステージ種別ごとの設定パターン

各ステージ種別の典型的な設定値パターン。新規ステージ作成時の参考として使用する。

---

## 共通デフォルト値

すべてのステージ種別で共通する設定:

| カラム | 値 | 説明 |
|--------|-----|------|
| `ENABLE` | `e` | 全テーブルで有効 |
| `normal_enemy_hp_coef` | `1` | MstInGame: 通常敵HP倍率（基本は等倍） |
| `normal_enemy_attack_coef` | `1` | MstInGame: 通常敵攻撃倍率 |
| `normal_enemy_speed_coef` | `1` | MstInGame: 通常敵速度倍率 |
| `boss_enemy_hp_coef` | `1` | MstInGame: ボス敵HP倍率 |
| `boss_enemy_attack_coef` | `1` | MstInGame: ボス敵攻撃倍率 |
| `boss_enemy_speed_coef` | `1` | MstInGame: ボス敵速度倍率 |
| `auto_lap_type` | `AfterClear` | MstStage: クリア後オートラップ |
| `max_auto_lap_count` | `5` | MstStage: 最大オートラップ回数 |
| `death_type` | `Normal` | MstAutoPlayerSequence: 通常死亡演出 |
| `aura_type` | `Default` | MstAutoPlayerSequence: 雑魚はDefault |

---

## event_charaget（キャラゲット型イベントクエスト）

最もよく使うパターン。ボスありの砦破壊型。

### MstEnemyOutpost
| カラム | 値 |
|--------|-----|
| `hp` | `20,000 〜 100,000` |
| `is_damage_invalidation` | 空（ダメージ有効） |
| `artwork_asset_key` | `{シリーズ略称}_{番号}` |

### MstStage
| カラム | 値 |
|--------|-----|
| `recommended_level` | `10 〜 30` |
| `cost_stamina` | `5 〜 15` |
| `exp` | `30 〜 200` |
| `coin` | `50 〜 500` |

### MstStageEventSetting
| カラム | 値 |
|--------|-----|
| `reset_type` | `__NULL__`（リセットなし）|
| `clearable_count` | `__NULL__`（制限なし）|

### MstStageEventReward（初回クリア）
| カラム | 値 |
|--------|-----|
| `reward_category` | `FirstClear` |
| `resource_type` | `FreeDiamond` |
| `resource_amount` | `20 〜 50` |

### MstAutoPlayerSequence: シーケンス行数目安
- `3 〜 6行`（ボス1体 + 時間差雑魚2〜3パターン）
- グループ切り替え: 通常なし

---

## event_challenge（チャレンジクエスト）

タイムアタック要素あり。SpeedAttack必須。

### MstEnemyOutpost
| カラム | 値 |
|--------|-----|
| `hp` | `30,000 〜 80,000` |

### MstInGameSpecialRule（必須）
| カラム | 値 |
|--------|-----|
| `rule_type` | `SpeedAttack` |
| `rule_value` | `140000`（140秒） または `120000`（120秒） |
| `content_type` | `Stage` |

SpeedAttack + NoContinue の組み合わせが基本パターン:
```
行1: rule_type=SpeedAttack, rule_value=140000
行2: rule_type=NoContinue, rule_value=1
```

### MstStageClearTimeReward（報酬設定）
| カラム | 値 |
|--------|-----|
| `upper_clear_time_ms` | `140000`（140秒） |
| `resource_type` | `FreeDiamond` |
| `resource_amount` | `20` |

### MstAutoPlayerSequence: シーケンス行数目安
- `4 〜 22行`
- FriendUnitDeadでSwitchSequenceGroupを組み合わせ

---

## event_savage（サベージバトル）

高難易度チャレンジ。チャレンジよりさらに強力な敵構成。

### MstEnemyOutpost
| カラム | 値 |
|--------|-----|
| `hp` | `50,000 〜 150,000` |

### MstInGameSpecialRule
チャレンジと同様にSpeedAttack + NoContinueを設定。

### MstEnemyStageParameter（ボス）
| カラム | 値の目安 |
|--------|---------|
| `enemy_hp_coef`（シーケンス） | `5 〜 20` |
| `enemy_attack_coef`（シーケンス） | `2 〜 5` |

### MstAutoPlayerSequence: シーケンス行数目安
- `6 〜 25行`
- ボスを複数体出すパターンが多い

---

## event_1day（1日限定クエスト）

毎日リセットされる簡易クエスト。シンプルな敵構成。

### MstEnemyOutpost
| カラム | 値 |
|--------|-----|
| `hp` | `5,000 〜 30,000` |

### MstStageEventSetting
| カラム | 値 |
|--------|-----|
| `reset_type` | `Daily` |
| `clearable_count` | `1` |

### MstAutoPlayerSequence: シーケンス行数目安
- `1 〜 3行`
- ElapsedTimeで雑魚を順番に出すだけのシンプルパターン

---

## raid（レイドバトル）

スコアアタック型。砦HPは無限（ダメージ無効）。

### MstEnemyOutpost
| カラム | 値 |
|--------|-----|
| `hp` | `1,000,000`（固定） |
| `is_damage_invalidation` | `1`（必須） |

### MstStage
| カラム | 値 |
|--------|-----|
| `auto_lap_type` | `__NULL__`（ループしない） |

### MstAutoPlayerSequence: シーケンス行数目安
- `30 〜 50行`
- グループが多段階: デフォルト → w1 → w2 → ... → wN → w1（ループ）
- defeated_score に得点を設定（通常ステージは0）

---

## normal / hard / veryhard（フリークエスト）

常設ステージ。難易度に応じてHPや強さを調整。

### MstEnemyOutpost（HP目安）
| 難易度 | HP |
|--------|-----|
| `normal` | `5,000 〜 60,000` |
| `hard` | `50,000 〜 150,000` |
| `veryhard` | `100,000 〜 300,000` |

### MstStageEventSetting
| カラム | 値 |
|--------|-----|
| `reset_type` | `__NULL__` |
| `clearable_count` | `__NULL__` |

### MstAutoPlayerSequence: シーケンス行数目安
| 難易度 | 行数目安 |
|--------|---------|
| `normal` | `3 〜 8行` |
| `hard` | `5 〜 15行` |
| `veryhard` | `8 〜 25行` |

---

## BGMアセットキーの目安

| 用途 | アセットキー例 |
|------|-------------|
| 通常BGM | `SSE_SBG_003_001` |
| ボスBGM | `SSE_SBG_003_004` |

実際のBGMキーは既存データから確認することを推奨:
```sql
SELECT DISTINCT bgm_asset_key, boss_bgm_asset_key FROM read_csv('projects/glow-masterdata/MstInGame.csv', AUTO_DETECT=TRUE) LIMIT 20;
```

---

## release_key の目安

新規データ作成時は、投入するリリースキーを設定する。
分からない場合は `999999999`（開発テスト用）を使い、後で変更する。
