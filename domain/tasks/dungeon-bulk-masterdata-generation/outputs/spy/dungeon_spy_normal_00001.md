# dungeon_spy_normal_00001 インゲーム要件テキスト

> コンテンツ種別: dungeon（限界チャレンジ）/ ブロック種別: normal（通常ブロック）
> シリーズ: SPY×FAMILY（spy）

---

## 概要

SPY×FAMILYシリーズの限界チャレンジ（dungeon）通常ブロック第1弾（dungeon_normal）。砦HPは100（固定）でダメージ有効（砦破壊型）。BGMは`SSE_SBG_003_002`、ループ背景は`spy_00005`。3行構成のコマフィールドを使用（dungeon_normal固定）し、行1は2コマ（幅0.5＋幅0.5）、行2は2コマ（幅0.4＋幅0.6）、行3は1コマ（幅1.0）。コマ効果はすべてなし（None）。

登場する敵は2種類。密輸組織の残党（`enemy_spy_00001`）の無属性（Colorless）・アタック型がHP 1,000・攻撃力 5,000・移動速度 45で出現し、グエン（`enemy_spy_00101`）の無属性（Colorless）・アタック型がHP 1,000・攻撃力 5,000・移動速度 42で出現する。どちらも必殺ワザなし・Normal種別で、dungeon専用の高難度設定として攻撃力は通常ステージの約100倍に設定されている。

グループ切り替えなしのシングルグループ構成（シーケンス計5行）。ElapsedTimeトリガーと撃破数トリガー（FriendUnitDead）を交互に組み合わせたウェーブ設計で、開始0.5秒後に密輸組織の残党が3体、2.0秒後にグエン2体、2体撃破後に密輸組織の残党2体、5.0秒後にグエン2体、5体撃破後に密輸組織の残党1体、計10体が順次出現する。dungeon_normal の特性として味方は1体操作のみのアクションRPG形式で、敵は味方の進行に反応して動き出す受動的な構成となっている。

ステージ説明では「無属性の敵のみが登場。属性相性の影響を受けないため、総合力の高いキャラを選ぼう」と案内されている。

---

## 設計情報（masterdata-ingame-creator 入力用）

| 項目 | 値 |
|------|-----|
| インゲームID | `dungeon_spy_normal_00001` |
| コンテンツ種別 | dungeon（限界チャレンジ） |
| ブロック種別 | normal（通常ブロック） |
| シリーズID | spy（SPY×FAMILY） |
| BGM | `SSE_SBG_003_002` |
| ループ背景 | `spy_00005` |
| 砦HP | 100（固定） |
| コマ行数 | 3行（固定） |
| コマエフェクト | なし（None） |

### 登場する敵

| 識別用ID | enemy_character_id | 属性 | ロール | HP | 攻撃力 | 速度 | 必殺 |
|---------|-------------------|------|-------|-----|-------|------|-----|
| `e_spy_00001_spy_dungeon_Normal_Colorless` | enemy_spy_00001 | Colorless | Attack | 1,000 | 5,000 | 45 | なし |
| `e_spy_00101_spy_dungeon_Normal_Colorless` | enemy_spy_00101 | Colorless | Attack | 1,000 | 5,000 | 42 | なし |

### シーケンス（MstAutoPlayerSequence）

| # | トリガー | 条件値 | 召喚エネミー | 体数 |
|---|---------|-------|-------------|-----|
| 1 | ElapsedTime | 500ms | 密輸組織の残党（Colorless） | 3体 |
| 2 | ElapsedTime | 2,000ms | グエン（Colorless） | 2体 |
| 3 | FriendUnitDead | 累計2体撃破 | 密輸組織の残党（Colorless） | 2体 |
| 4 | ElapsedTime | 5,000ms | グエン（Colorless） | 2体 |
| 5 | FriendUnitDead | 累計5体撃破 | 密輸組織の残党（Colorless） | 1体 |
