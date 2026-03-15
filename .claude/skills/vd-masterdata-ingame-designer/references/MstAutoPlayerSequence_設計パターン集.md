# MstAutoPlayerSequence 設計パターン集

過去の本番データを分析し、インゲーム設計書をより深みのある内容で作成するための参照ガイドです。

---

## 1. action_type の種類と用途

| action_type | 件数 | 用途 |
|------------|------|------|
| **SummonEnemy** | 4,400 | 敵/フレンドユニットを召喚する（メイン） |
| **TransformGimmickObjectToEnemy** | 41 | ギミックオブジェクトを敵に変換する |
| **SummonGimmickObject** | 27 | ギミックオブジェクトを召喚する |

---

## 2. condition_type の全バリエーション

### 2-1. 時間系トリガー（最多・最も基本的）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **ElapsedTime** | 1,628 | ステージ開始からの経過時間（**100ms単位**。250=2500ms） | 250, 1500, 3000, 5000... |

**設計ポイント**:
- VDブロックの基本構成は `ElapsedTime` の連続で十分
- `action_delay` でさらに細かいオフセットを追加可能

### 2-2. フレンドユニット数トリガー（最もゲーム性に影響する）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **FriendUnitDead** | 1,463 | 指定したsequence_element_idで召喚したキャラが1体でも倒されたら発火 | 参照するsequence_element_id（1〜35まで幅広い） |
| **FriendUnitSummoned** | 9 | フレンドユニットが合計N体召喚された時 | 2, 3, 4, 8 |
| **FriendUnitTransform** | 20 | フレンドユニットが変身した時（常に1） | 1 |

**FriendUnitDead の使い方（追加召喚トリガーとして）**:
```
FriendUnitDead=3  → 強化雑魚 summon_count=3 を追加召喚
FriendUnitDead=10 → c_キャラ（ボスオーラ）1体が登場
FriendUnitDead=20 → 雑魚 summon_count=99 の無限補充に切り替え
```

**設計ポイント**:
- 「N体倒す → 追加敵が来る」というプレッシャーをかける
- 値が大きいほど後半の強化（10〜35体でボス級キャラ登場など）

### 2-3. 初期召喚（ステージ開幕時）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **InitialSummon** | 357 | ステージ開始時に即召喚 | 0, 1, 2（召喚順を制御） |

**設計ポイント**:
- 値は召喚の順序を制御（0→1→2の順に処理）
- ボスを最初から配置する場合や、開幕演出として使用
- VDのシンプルな構成では `ElapsedTime` の 0〜数百ms で代替することも多い

### 2-4. コマ進行トリガー（位置依存）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **EnterTargetKomaIndex** | 229 | プレイヤーが特定のコマインデックスに入った時 | 0〜7 |

**設計ポイント**:
- コマ番号（0〜7）に対応してイベントを発生させる
- ステージの地形に合わせた配置演出に使う
- VDでは使用されていないが、コマ連動の演出に活用できる

### 2-5. 拠点ダメージトリガー（防衛のプレッシャー）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **OutpostHpPercentage** | 155 | 拠点HPが指定%以下になった時 | 99, 90, 80, 70, 60, 50, 40, 30, 10 |
| **OutpostDamage** | 136 | 拠点が受けたダメージが指定値以上になった時 | 1, 1000, 2000, 5000, 7000 |

**OutpostHpPercentage の典型的な使い方**:
```
OutpostHpPercentage=99 → 実質「拠点に初ダメージが入ったら」即座に強化（99%が最多）
OutpostHpPercentage=50 → 半分削れたらボス強化
OutpostHpPercentage=30 → 追い込まれた時の最終強化
```

**OutpostDamage の使い方**:
- `OutpostDamage=1` は「拠点に1でもダメージが入ったら」= 実質初ダメージトリガー
- c_rik（rikキャラ）ステージで `OutpostDamage=1` で `c_rik` を大量召喚するパターンが多い

### 2-6. ギミック系トリガー（特殊コンテンツ）

| condition_type | 件数 | condition_value の意味 | 典型的な値 |
|---------------|------|----------------------|----------|
| **DarknessKomaCleared** | 41 | 闇コマをN個クリアした時 | 1〜5 |

**設計ポイント**:
- 闇コマをクリアするほど強い敵が出る「難易度自動調整」的な設計
- ダンジョン系コンテンツで使用（DAN作品など）

---

## 3. e_キャラ vs c_キャラ の使い分けパターン

### 比率
- **e_キャラ（純粋な敵ユニット）**: 3,462件（78%）
- **c_キャラ（フレンドユニットの敵バージョン）**: 938件（22%）

### e_キャラの命名規則
```
e_{作品ID}_{キャラ連番}_{コンテキスト名}_{難易度}_{unitType}_{color}
例: e_kai_00101_general_kai_vh_Normal_Yellow
例: e_glo_00001_vd_Normal_Colorless  ← VD汎用の雑魚
```

**コンテキスト名パターン**:
- `general_n` / `general_h` / `general_vh` - 難易度別汎用
- `vd` - 限界チャレンジ専用
- `mainquest` - メインクエスト専用
- `charaget01` / `charaget02` - キャラゲット専用
- `advent` - 来訪イベント専用
- `savage` / `savage01` - サベージ専用
- `challenge` - チャレンジ専用

### c_キャラの命名規則
```
c_{作品ID}_{キャラ連番}_{コンテキスト名}_{unitType}_{color}
例: c_rik_00001_general_Normal_Colorless  ← リクキャラの汎用
例: c_tak_00001_mainquest_Boss_Blue      ← タクトキャラのボス
例: c_spy_00201_damianget_Boss_Red       ← スパイファミリーのダミアン
```

### c_キャラが使われる典型的なシチュエーション

| シチュエーション | 条件 | 代表例 |
|--------------|------|------|
| **メインキャラが敵として登場するステージ** | 作品の主人公を「敵バージョン」で登場させる | `c_rik`, `c_tak`, `c_dan` |
| **ストーリー演出** | ボス戦でキャラが敵対する場面 | `c_spy_00201_damianget_Boss_Red` |
| **VDフレンドユニット登場** | プレイヤーが戦うフレンドキャラ | `c_rik_00001_general_Normal_Colorless` |
| **イベントのキャラゲット戦** | イベントでキャラを獲得するための戦い | `c_hut_00001_hut1_charaget01_Normal_Colorless` |

### c_キャラのcondition_type分布（e_キャラとの違い）

| condition_type | c_キャラ件数 | 特徴 |
|---------------|-----------|------|
| FriendUnitDead | 337 | 最多。「N体倒されたら強化キャラ召喚」 |
| ElapsedTime | 300 | 時間経過で定期的にメインキャラが登場 |
| InitialSummon | 64 | 開幕からメインキャラをボスとして配置 |
| EnterTargetKomaIndex | 48 | コマ進行でメインキャラが迎え撃つ |
| OutpostHpPercentage | 36 | 拠点が削れるとメインキャラが覚醒登場 |
| OutpostDamage | 22 | 拠点ダメージで主人公キャラが怒って登場 |
| DarknessKomaCleared | 13 | 闇コマクリアに反応 |

---

## 4. summon_count・summon_interval のパターン（VD）

### summon_count（召喚数）のよく使う値

| 値 | 意図 | 使用例 |
|----|------|------|
| **1** | 1体ずつ精密召喚 | ボス、特殊キャラ、演出召喚 |
| **2〜5** | 小グループ | 中程度の密度 |
| **10〜20** | 大量召喚 | 雑魚ラッシュ、波攻撃 |
| **30〜50** | 大規模ラッシュ | 強化フェーズの物量攻撃 |
| **99** | 実質無限召喚 | 終盤の永続出現（1匹倒されたら1体補充） |

### summon_interval（召喚間隔ms）のよく使う値

| 値 | 意図 |
|----|------|
| **0** | 同時召喚（まとめて一気に） |
| **50〜100** | 非常に素早い連続召喚 |
| **200〜500** | 適度な間隔でのラッシュ |
| **700〜1200** | ゆっくりめの定期召喚 |

**実用的な組み合わせ例**:
```
# 終盤の無限補充
summon_count=99, summon_interval=500
# 開幕一気に展開
summon_count=5, summon_interval=0
# ウェーブ攻撃
summon_count=10, summon_interval=750
```

---

## 5. summon_position（召喚位置）パターン

- 空白（デフォルト）: ランダムまたはシステム決定
- **0.7〜0.9**: 前方寄り（敵陣に近い側）
- **1.3〜1.8**: 中間
- **2.5〜2.9**: 後方寄り（拠点に近い側）

**設計ポイント**: 複数の同じ敵を違う位置に配置するとき、同じ `sequence_element_id` で `summon_position` だけ変えた複数行を作る。

---

## 6. aura_type のバリエーション

| aura_type | 件数 | 意味 |
|-----------|------|------|
| **Default** | 3,919 | 通常の敵（オーラなし） |
| **Boss** | 617 | ボスオーラ（大きなリング演出） |
| **AdventBoss1** | 36 | 来訪ボスオーラ（バリエーション1） |
| **AdventBoss2** | 37 | 来訪ボスオーラ（バリエーション2） |
| **AdventBoss3** | 32 | 来訪ボスオーラ（バリエーション3） |

---

## 7. summon_animation_type のバリエーション

| summon_animation_type | 件数 | 意味 |
|----------------------|------|------|
| **None** | 4,179 | 通常の召喚アニメーション |
| **Fall** | 280 | 落下して出現 |
| **Fall0** | 121 | 落下バリエーション0 |
| **Fall4** | 61 | 落下バリエーション4 |

---

## 8. VD専用設計の特徴（実データより）

既存 `vd_kai_normal_00001` の構成:
```
全5要素、単一グループ（sequence_group_id=空）
ElapsedTime=250   → e_glo_00001_vd_Normal_Colorless  5体 (interval=0)
ElapsedTime=1500  → e_kai_00101_vd_Normal_Yellow      4体 (interval=0)
ElapsedTime=3000  → e_glo_00001_vd_Normal_Colorless  5体 (interval=0)
ElapsedTime=5000  → e_kai_00101_vd_Normal_Yellow      4体 (interval=0)
ElapsedTime=7000  → e_kai_00101_vd_Normal_Yellow      4体 (interval=0)
```

**VD設計の基本パターン**:
- 全要素 `enemy_hp_coef=1.0, enemy_attack_coef=1.0, enemy_speed_coef=1.0`
- `aura_type=Default, death_type=Normal`
- `summon_animation_type=None`
- `is_summon_unit_outpost_damage_invalidation=0`（拠点ダメージ有効）

---

## 9. 設計書に深みを持たせるための推奨パターン

### シンプルVDノーマル（現行）
```
時間トリガーのみ、単一グループ、雑魚+作品キャラの繰り返し
```

### 深みを加える設計案

#### A. FriendUnitDead トリガー型（倒すほど強くなる）
```
ElapsedTime=500  → 雑魚5体
ElapsedTime=3000 → 作品キャラ（Normal）3体
FriendUnitDead=5  → 強化雑魚 summon_count=5 を追加召喚
FriendUnitDead=15 → c_キャラ（Boss オーラ）1体が登場
FriendUnitDead=25 → 雑魚 summon_count=99（終盤無限補充）
```

#### B. 拠点防衛プレッシャー型（OutpostHpPercentage）
```
ElapsedTime=500  → 雑魚を定期召喚
OutpostHpPercentage=80 → 強化雑魚を追加召喚
OutpostHpPercentage=50 → c_キャラ（覚醒ボス）が登場
```

#### C. ストーリー演出型（InitialSummon + c_キャラ）
```
InitialSummon=0  → c_キャラ（ボス）1体（開幕からメインキャラが立ちはだかる）
ElapsedTime=1000 → 雑魚を定期召喚
FriendUnitDead=1 → 強化雑魚 summon_count=3 を追加
FriendUnitDead=10 → 作品キャラ（Normal・色違い）を追加召喚
```

#### D. キャラ変身トリガー型（FriendUnitTransform）
```
FriendUnitTransform=1 → 変身後に雑魚を大量召喚
（フレンドが変身してから敵が本気を出すシチュエーション）
```

---

## 10. 関連ドキュメント・参照元データ

- **具体例集**: `MstAutoPlayerSequence_具体例集.md` — e_キャラのみ5例・c_キャラ含む5例の実データ（合計15体以上・単一グループ）

## 11. 参照元データ

- **本番データ件数**: 4,641件（MstAutoPlayerSequence.csv）
- **VD既存ブロック**: `vd-ingame-design-creator/vd_kai_normal_00001/`
- **分析日**: 2026-03-12
