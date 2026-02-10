# リソース別手順書分割計画

## 概要

現在「原画・エンブレム」および「アイテム・報酬」として統合されている手順書を、各リソースごとに独立した手順書に分割する計画です。将来的に類似のリソースが増える可能性を考慮し、各リソースが独立してメンテナンス可能な構成を実現します。

## 背景と目的

### 背景
- 現在の手順書は複数のリソースを1つにまとめている
- 将来的に類似リソースが追加される可能性がある
- 手順書の責務が多いとメンテナンスが困難になる

### 目的
- 各リソースごとに独立した手順書を作成
- リソース単位でのメンテナンス性向上
- 将来的な拡張性の確保
- テーブル間の依存関係の明確化

## 現状分析

### 原画・エンブレム手順書の構成

**対象テーブル**: 7テーブル

**原画関連 (5テーブル)**:
1. **MstArtwork** - 原画の基本情報（レアリティ、拠点追加HP等）
2. **MstArtworkI18n** - 原画名・説明文（多言語対応）
3. **MstArtworkFragment** - 原画の欠片情報（ドロップ率、レアリティ等）
4. **MstArtworkFragmentI18n** - 原画の欠片名（多言語対応）
5. **MstArtworkFragmentPosition** - 原画の欠片の配置位置（1~16）

**エンブレム関連 (2テーブル)**:
6. **MstEmblem** - エンブレムの基本情報（タイプ、シリーズID等）
7. **MstEmblemI18n** - エンブレム名・説明文（多言語対応）

### アイテム・報酬手順書の構成

**対象テーブル**: 3テーブル

**アイテム関連 (2テーブル)**:
1. **MstItem** - アイテムの基本情報（タイプ、レアリティ、効果値等）
2. **MstItemI18n** - アイテム名・説明文（多言語対応）

**報酬関連 (1テーブル)**:
3. **MstMissionReward** - ミッション報酬の定義（リソースタイプ、数量等）

## テーブル間の依存関係分析

### 原画テーブル群の依存関係

```
MstArtwork (親)
  ├─→ MstArtworkI18n (子: mst_artwork_id で参照)
  └─→ MstArtworkFragment (子: mst_artwork_id で参照)
        ├─→ MstArtworkFragmentI18n (孫: mst_artwork_fragment_id で参照)
        └─→ MstArtworkFragmentPosition (孫: mst_artwork_fragment_id で参照)
```

**依存関係の特徴**:
- 原画5テーブルは強く結合している
- MstArtworkを親として階層的な依存関係
- 1つの原画につき必ず16個の欠片が必要（4×4グリッド）
- 3つの欠片テーブルは連動して作成する必要がある

**独立性**: ★☆☆☆☆ (低い)
- 原画の欠片は原画本体に強く依存
- 分割は現実的ではない

### エンブレムテーブル群の依存関係

```
MstEmblem (親)
  └─→ MstEmblemI18n (子: mst_emblem_id で参照)
```

**依存関係の特徴**:
- エンブレム2テーブルは独立している
- 他のテーブルへの依存なし
- シンプルな親子関係

**独立性**: ★★★★★ (高い)
- 完全に独立したリソース
- 分割に適している

### アイテムテーブル群の依存関係

```
MstItem (親)
  └─→ MstItemI18n (子: mst_item_id で参照)
```

**依存関係の特徴**:
- アイテム2テーブルは独立している
- 他のテーブルへの依存なし
- シンプルな親子関係

**独立性**: ★★★★★ (高い)
- 完全に独立したリソース
- 分割に適している

### 報酬テーブル群の依存関係

```
全17個の報酬テーブル
  ├─→ MstItem (参照: resource_type=Item の場合、resource_id で参照)
  ├─→ MstUnit (参照: resource_type=Unit の場合、resource_id で参照)
  └─→ MstEmblem (参照: resource_type=Emblem の場合、resource_id で参照)
```

**依存関係の特徴**:
- 報酬テーブルは他のリソースを参照する共通パターンを持つ
- **resource_type別の参照先**:
  - `resource_type=Item` → `MstItem.id` を参照（アイテム報酬）
  - `resource_type=Unit` → `MstUnit.id` を参照（ユニット報酬）
  - `resource_type=Emblem` → `MstEmblem.id` を参照（エンブレム報酬）
  - `resource_type=Coin/FreeDiamond/PaidDiamond` → 参照なし（通貨報酬）
- 一方向の参照（参照先テーブルから見て依存なし）
- 全17個の報酬テーブルが同じ依存パターンを共有

**使用されるresource_type（報酬テーブル別）**:
- MstMissionReward: Coin, FreeDiamond, Item, Emblem
- MstStageReward: Coin, FreeDiamond, Item, Emblem
- MstStageEventReward: Coin, FreeDiamond, Item, Emblem, Unit
- MstAdventBattleReward: Coin, FreeDiamond, Item, Emblem
- MstPvpReward: Coin, FreeDiamond, Item
- MstExchangeReward: Item, Unit, Emblem
- MstDailyBonusReward: Coin, FreeDiamond, Item
- MstShopPassReward: Coin, FreeDiamond
- その他の報酬テーブルも同様のパターン

**独立性**: ★★★☆☆ (中程度)
- MstItem、MstUnit、MstEmblemへの依存があるが、一方向の参照
- 分割可能だが、参照先テーブルとの関係を明示する必要あり
- 全報酬テーブルが共通の依存パターンを持つため、汎用スキルとしてまとめることが適切

## 分割方針

### 基本方針

1. **リソースの独立性を重視**
   - 独立性の高いリソースから優先的に分割
   - 強く結合しているテーブル群は1つの手順書にまとめる

2. **メンテナンス性の向上**
   - 各手順書は1つのリソースに特化
   - 手順書間の依存関係を最小化

3. **拡張性の確保**
   - 将来的な類似リソース追加に対応しやすい構成
   - 手順書の責務を明確化

### 分割後の構成

現在の2つの手順書を、以下の**4つの独立した手順書**に分割します。

```
現在:
├── 原画・エンブレム (7テーブル)
└── アイテム・報酬 (3テーブル)

分割後:
├── 原画 (5テーブル)  ← 強く結合しているため統合維持
├── エンブレム (2テーブル)  ← 独立
├── アイテム (2テーブル)  ← 独立
└── 報酬 (1テーブル)  ← 独立（アイテムへの参照を明示）
```

## 分割後の各手順書の詳細

### 1. 原画手順書

**ファイル名**: `原画_マスタデータ設定手順書.md`

**対象テーブル**: 5テーブル
- MstArtwork
- MstArtworkI18n
- MstArtworkFragment
- MstArtworkFragmentI18n
- MstArtworkFragmentPosition

**責務**:
- 原画本体の設定
- 原画の欠片の設定（16個の欠片、4×4グリッド）
- 欠片のドロップグループ設定
- 欠片の配置位置設定

**主要な設定項目**:
- 原画ID、シリーズID、レアリティ
- 拠点追加HP
- 欠片のドロップグループID、ドロップ率
- 欠片の配置位置（1~16）

**外部依存**: なし

**注意点**:
- 1つの原画につき必ず16個の欠片を作成
- 3つの欠片テーブル（Fragment、FragmentI18n、FragmentPosition）は連動して作成

### 2. エンブレム手順書

**ファイル名**: `エンブレム_マスタデータ設定手順書.md`

**対象テーブル**: 2テーブル
- MstEmblem
- MstEmblemI18n

**責務**:
- エンブレムの基本設定
- エンブレムの多言語対応

**主要な設定項目**:
- エンブレムID、エンブレムタイプ（Event、Series）
- シリーズID
- アセットキー
- エンブレム名・説明文（多言語）

**外部依存**: なし

**注意点**:
- 降臨バトルエンブレムはランキング順位に応じて複数作成（通常6個）
- エンブレムタイプの設定を正確に行う

### 3. アイテム手順書

**ファイル名**: `アイテム_マスタデータ設定手順書.md`

**対象テーブル**: 2テーブル
- MstItem
- MstItemI18n

**責務**:
- アイテムの基本設定
- アイテムの多言語対応

**主要な設定項目**:
- アイテムID、アイテムタイプ（CharacterFragment、RankUpMaterial、Ticket等）
- グループタイプ、レアリティ
- 効果値（キャラメモリーの場合はキャラID）
- 開始・終了日時
- アイテム名・説明文（多言語）

**外部依存**: なし

**注意点**:
- type=RankUpMaterialの場合、effect_valueにキャラIDを設定
- type=CharacterFragmentの場合、effect_valueは空欄

**参照元**:
- MstMissionReward.resource_id から参照される（resource_type=Itemの場合）

### 4. 報酬設定手順書（汎用）

**ファイル名**: `報酬設定_マスタデータ設定手順書.md`

**位置付け**: **報酬設定用汎用スキル**

**対象テーブル**: 17テーブル（全ての報酬関連テーブル）
- MstMissionReward（ミッション報酬）
- MstStageReward（ステージ報酬）
- MstStageEventReward（ステージイベント報酬）
- MstAdventBattleReward（降臨バトル報酬）
- MstPvpReward（PVP報酬）
- MstExchangeReward（交換報酬）
- MstShopPassReward（ショップパス報酬）
- MstDailyBonusReward（デイリーボーナス報酬）
- MstIdleIncentiveReward（放置報酬）
- MstUnitEncyclopediaReward（ユニット図鑑報酬）
- MstStageClearTimeReward（ステージクリア時間報酬）
- MstStageEnhanceRewardParam（ステージ強化報酬パラメータ）
- MstEventDisplayReward（イベント表示報酬）
- MstAdventBattleClearReward（降臨バトルクリア報酬）
- MstAdventBattleRewardGroup（降臨バトル報酬グループ）
- MstPvpRewardGroup（PVP報酬グループ）
- MstStageRewardGroup（ステージ報酬グループ）

**責務**:
- **報酬設定の共通ルール**を提供
  - リソースタイプの定義（Item、Coin、FreeDiamond、PaidDiamond、Ticket等）
  - リソースIDの設定ルール
  - リソース数量の設定ルール
  - 報酬グループIDの命名規則
- **各機能ごとの固有設定**を拡張要素として記載
  - ミッション報酬の固有ルール
  - ステージ報酬の固有ルール
  - 降臨バトル報酬の固有ルール
  - PVP報酬の固有ルール
  - その他の報酬の固有ルール

**主要な共通設定項目**:
- 報酬ID、報酬グループID
- リソースタイプ（Item、Unit、Emblem、Coin、FreeDiamond、PaidDiamond等）
- リソースID（resource_typeに応じて参照先が変わる）
- リソース数量
- 重み（ランダム排出の場合）
- 表示順序

**外部依存（resource_type別）**:
- **MstItem** (resource_type=Itemの場合)
  - resource_idはMstItem.idを参照
  - アイテム手順書で作成されたアイテムIDを使用
  - 例: box_glo_*, memory_*, memoryfragment_*, ticket_*, item_*, entry_item_*
- **MstUnit** (resource_type=Unitの場合)
  - resource_idはMstUnit.idを参照
  - ヒーロー手順書で作成されたユニットIDを使用
  - 例: chara_*
- **MstEmblem** (resource_type=Emblemの場合)
  - resource_idはMstEmblem.idを参照
  - エンブレム手順書で作成されたエンブレムIDを使用
  - 例: emblem_event_*, emblem_adventbattle_*
- **参照なし** (resource_type=Coin/FreeDiamond/PaidDiamondの場合)
  - resource_idは空欄

**使用方法**:
1. **単独使用**: 報酬設定のみを行う場合
2. **併用**: 各機能ごとのマスタデータ作成スキル（ガチャ、ヒーロー、ミッション等）と併用して、報酬設定を高精度に実行

**注意点**:
- resource_type別にresource_idの参照先が異なる（Item→MstItem、Unit→MstUnit、Emblem→MstEmblem）
- resource_type=Coin/FreeDiamond/PaidDiamondの場合、resource_idは空欄
- 報酬グループIDの命名規則を遵守
- 各機能ごとの固有ルールは、対応するセクションを参照

**依存関係の明示**:
```
報酬テーブル.resource_id → MstItem.id (resource_type=Item の場合)
報酬テーブル.resource_id → MstUnit.id (resource_type=Unit の場合)
報酬テーブル.resource_id → MstEmblem.id (resource_type=Emblem の場合)
報酬テーブル.resource_id → 空欄 (resource_type=Coin/FreeDiamond/PaidDiamond の場合)
```

## 分割のメリット

### 1. メンテナンス性の向上
- 各リソースごとに独立した手順書
- 変更時の影響範囲が明確
- 手順書のサイズが適切になる（各手順書300~600行程度）

### 2. 責務の明確化
- 各手順書は1つのリソースに特化
- 手順書間の責務の重複がない
- 新規メンバーの理解が容易

### 3. 拡張性の確保
- 将来的な類似リソース追加が容易
- 既存の手順書への影響を最小化
- 手順書のテンプレート化が可能

### 4. 再利用性の向上
- 各リソースを個別に作成可能
- 例: エンブレムのみを追加する運営施策
- 例: アイテムのみを追加する運営施策

## 分割の注意点

### 1. 原画と欠片の一体性
- 原画5テーブルは強く結合しているため、1つの手順書にまとめる
- 分割すると逆に複雑になる

### 2. 報酬とアイテムの依存関係
- 報酬手順書はアイテムIDを参照する
- アイテム手順書で作成したIDを使用する必要がある
- 手順書内で依存関係を明示

### 3. 手順書間の参照
- 各手順書は独立しているが、依存関係がある場合は明示
- 例: 報酬手順書 → アイテム手順書を参照

## 実装ロードマップ

### フェーズ1: 原画・エンブレムの分割
1. 既存の「原画・エンブレム手順書」を読み込み
2. 原画関連（5テーブル）を抽出して「原画手順書」を作成
3. エンブレム関連（2テーブル）を抽出して「エンブレム手順書」を作成
4. 各手順書の検証

### フェーズ2: アイテム・報酬の分割
1. 既存の「アイテム・報酬手順書」を読み込み
2. アイテム関連（2テーブル）を抽出して「アイテム手順書」を作成
3. 報酬関連（17テーブル）を抽出して「報酬設定手順書（汎用）」を作成
   - 報酬設定の共通ルールをまとめる
   - 各機能ごとの固有設定を拡張要素として記載
   - アイテム、ユニットへの依存関係を明示
4. 各手順書の検証

### フェーズ3: 統合テストと検証
1. 分割後の各手順書で実際にマスタデータを作成
2. テーブル間の整合性を検証
3. 既存の手順書と比較して漏れがないか確認

## ディレクトリ構成

```
manuals/
├── artwork/                    # 原画手順書（新規）
│   ├── 原画_マスタデータ設定手順書.md
│   └── 原画_プロンプト.md
│
├── emblem/                     # エンブレム手順書（新規）
│   ├── エンブレム_マスタデータ設定手順書.md
│   └── エンブレム_プロンプト.md
│
├── item/                       # アイテム手順書（新規）
│   ├── アイテム_マスタデータ設定手順書.md
│   └── アイテム_プロンプト.md
│
├── reward/                     # 報酬設定手順書（汎用・新規）
│   ├── 報酬設定_マスタデータ設定手順書.md
│   └── 報酬設定_プロンプト.md
│
├── artwork-emblem/             # 旧: 原画・エンブレム（削除予定）
│   ├── 原画・エンブレム_マスタデータ設定手順書.md
│   └── 原画・エンブレム_プロンプト.md
│
└── item-reward/                # 旧: アイテム・報酬（削除予定）
    ├── アイテム・報酬_マスタデータ設定手順書.md
    └── アイテム・報酬_プロンプト.md
```

## 各手順書の対象テーブル一覧

| 手順書 | 対象テーブル数 | テーブル一覧（代表例） | 独立性 / 位置付け |
|--------|--------------|-------------|--------|
| **原画** | 5 | MstArtwork, MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentI18n, MstArtworkFragmentPosition | ★★★★★ |
| **エンブレム** | 2 | MstEmblem, MstEmblemI18n | ★★★★★ |
| **アイテム** | 2 | MstItem, MstItemI18n | ★★★★★ |
| **報酬設定（汎用）** | 17 | MstMissionReward, MstStageReward, MstAdventBattleReward, MstPvpReward等 | ★★★☆☆ (MstItem/MstUnitに依存) / **汎用スキル** |

## まとめ

現在の2つの統合手順書を、4つの独立した手順書に分割します。

**分割の重点**:
1. **原画**: 強く結合した5テーブルを1つの手順書に
2. **エンブレム**: 完全に独立した2テーブル
3. **アイテム**: 完全に独立した2テーブル
4. **報酬設定（汎用）**: 17テーブル（全報酬テーブル）の共通ルールをまとめた汎用スキル
   - 各機能ごとのマスタデータ作成スキルと併用して、報酬設定を高精度に実行
   - 報酬設定の共通ルールと各機能ごとの固有設定を包括的にカバー

**期待効果**:
- メンテナンス性の向上
- 責務の明確化
- 拡張性の確保
- 再利用性の向上

**次のステップ**:
1. 原画手順書の作成
2. エンブレム手順書の作成
3. アイテム手順書の作成
4. 報酬手順書の作成
5. 統合テストと検証
