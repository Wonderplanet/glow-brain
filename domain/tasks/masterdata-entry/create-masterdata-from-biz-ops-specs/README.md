# マスタデータ一括作成システム

## 概要

このシステムは、GLOWゲームプロジェクトにおいて、**設計書・運営仕様書からマスタデータCSVを精度高く、漏れなく、間違いない状態で作成する**ための仕組みです。

### 背景

従来、マスタデータの作成は手作業で行われており、以下の課題がありました:
- 設計書の見落としによるデータの漏れ
- 採番ルールの誤適用
- データ整合性チェックの不足
- 作業の属人化

本システムは、リリースキー202601010（地獄楽いいジャン祭）を参考に、これらの課題を解決するために構築されました。

### システムの特徴

1. **100%カバレッジ** - 全79テーブルを網羅
2. **13グループの機能分類** - 機能ごとに手順書を整備
3. **階層的マッピング** - 設計書↔テーブルの対応関係を明確化
4. **詳細な手順書** - 採番ルール、enum値、チェック項目を完全記載
5. **推測値レポート** - 自動推測した値を明示

---

## ディレクトリ構成

```
domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/
├── README.md                          # 本ファイル
├── specs/                             # 設計書一覧
│   └── 202601010_地獄楽.csv          # 地獄楽の設計書パス一覧
├── analysis/                          # 分析結果
│   ├── input-documents-analysis.md    # 設計書15件の詳細分析
│   ├── output-masterdata-analysis.md  # マスタデータ79テーブルの分析
│   ├── reference-manual-structure.md  # 手順書テンプレート
│   └── input-output-mapping.md        # 設計書↔テーブルのマッピング
└── manuals/                           # 機能別手順書（全12グループ完成）
    ├── hero/                          # ヒーロー（ユニット）
    ├── quest-stage/                   # クエスト・ステージ
    ├── advent-battle/                 # 降臨バトル
    ├── mission/                       # ミッション
    ├── gacha/                         # ガチャ
    ├── item-reward/                   # アイテム・報酬
    ├── pvp/                           # PVP（ランクマッチ）
    ├── event-basic/                   # イベント基本設定
    ├── shop-pack/                     # ショップ・パック
    ├── artwork-emblem/                # 原画・エンブレム
    ├── enemy-autoplayer/              # 敵・自動行動
    └── ingame/                        # インゲーム（マンガアニメーション含む）
        ├── {機能名}_マスタデータ設定手順書.md
        └── {機能名}_プロンプト.md
```

---

## 使い方

### ステップ1: 設計書の準備

新しいリリースキーのマスタデータを作成する場合、以下のファイルを準備します:

1. **設計書一覧CSV**
   `specs/{release_key}_{コンテンツ名}.csv`

   フォーマット:
   ```csv
   description,path
   local,domain/raw-data/google-drive/spread-sheet/...
   ```

2. **設計書本体**
   設計書一覧CSVに記載されたパスに、実際の設計書ファイルを配置

### ステップ2: 機能別手順書の選択

作成するマスタデータの機能に応じて、適切な手順書を選択します:

| 機能グループ | 手順書パス | 対象テーブル数 | 状態 |
|------------|----------|--------------|------|
| **ヒーロー（ユニット）** | manuals/hero/ | 13テーブル | ✅ 完成 |
| **クエスト・ステージ** | manuals/quest-stage/ | 10テーブル | ✅ 完成 |
| **降臨バトル** | manuals/advent-battle/ | 7テーブル | ✅ 完成 |
| **ミッション** | manuals/mission/ | 8テーブル | ✅ 完成 |
| **ガチャ** | manuals/gacha/ | 6テーブル | ✅ 完成 |
| **アイテム・報酬** | manuals/item-reward/ | 3テーブル | ✅ 完成 |
| **PVP（ランクマッチ）** | manuals/pvp/ | 2テーブル | ✅ 完成 |
| **イベント基本設定** | manuals/event-basic/ | 3テーブル | ✅ 完成 |
| **ショップ・パック** | manuals/shop-pack/ | 7テーブル | ✅ 完成 |
| **原画・エンブレム** | manuals/artwork-emblem/ | 7テーブル | ✅ 完成 |
| **敵・自動行動** | manuals/enemy-autoplayer/ | 5テーブル | ✅ 完成 |
| **インゲーム（マンガアニメーション含む）** | manuals/ingame/ | 7テーブル | ✅ 完成 |

**全12グループの手順書が完成しています。**全79テーブルをカバーする包括的なマスタデータ作成システムが整備されました。

### ステップ3: 手順書に従ってマスタデータを作成

#### 3.1 プロンプトファイルの確認

各手順書ディレクトリにある`{機能名}_プロンプト.md`を開き、必要な入力パラメータを確認します。

例（ヒーロー）:
- release_key: 202601010
- mst_series_id: jig
- mst_unit_id: chara_jig_00401
- character_name: 賊王 亜左 弔兵衛

#### 3.2 Claude Codeでマスタデータ生成

1. プロンプトファイルの内容をClaude Codeに入力
2. 設計書ファイルを添付
3. 入力パラメータを指定
4. 実行

#### 3.3 出力の確認

以下の2つが出力されます:
1. **マスタデータ（Markdown表形式）** - スプレッドシートにコピー可能
2. **推測値レポート** - 自動推測した値のリスト（要確認）

#### 3.4 データ整合性チェック

手順書の「データ整合性のチェック」セクションを参照し、以下を確認:
- [ ] ヘッダーの列順が正しいか
- [ ] IDの一意性
- [ ] ID採番ルール
- [ ] リレーションの整合性
- [ ] enum値の正確性

#### 3.5 既存スキルでの検証（オプション）

```bash
# masterdata-csv-validatorスキルで検証
claude code /masterdata-csv-validator {作成したCSVファイルパス}
```

### ステップ4: CSVファイルのエクスポート

1. Markdown表をスプレッドシートにコピー
2. CSVとしてエクスポート
3. 適切なディレクトリに保存

---

## 分析結果の活用

### 設計書↔テーブルのマッピング

`analysis/input-output-mapping.md`を参照すると、以下が確認できます:
- どの設計書がどのテーブルに対応するか
- テーブルごとの対応設計書（逆引き）
- 設計書読み取りの推奨順序

### 手順書のテンプレート

新しい機能グループの手順書を作成する場合、以下を参考にしてください:
- `analysis/reference-manual-structure.md` - 手順書の構成テンプレート
- `manuals/hero/ヒーロー_マスタデータ設定手順書.md` - 実際の手順書の例

---

## 完成した手順書一覧

全12グループの手順書が完成しています。各グループには詳細な設定手順書とプロンプトファイルが含まれます。

### 優先度高（完成済み）

1. **ヒーロー（ユニット）** - 対象テーブル: 13個 ✅
   - MstUnit, MstUnitI18n, MstUnitAbility, MstAbility, MstAttack, MstAttackElement等

2. **クエスト・ステージ** - 対象テーブル: 10個 ✅
   - MstQuest, MstQuestI18n, MstStage, MstStageI18n, MstStageEventReward, MstStageEventSetting等

3. **降臨バトル** - 対象テーブル: 7個 ✅
   - MstAdventBattle, MstAdventBattleI18n, MstAdventBattleRank, MstAdventBattleReward等

4. **ミッション** - 対象テーブル: 8個 ✅
   - MstMissionEvent, MstMissionEventI18n, MstMissionEventDependency, MstMissionReward等

5. **ガチャ** - 対象テーブル: 6個 ✅
   - OprGacha, OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource等

### 優先度中（完成済み）

6. **アイテム・報酬** - 対象テーブル: 3個 ✅
   - MstItem, MstItemI18n, MstItemCategory

7. **PVP（ランクマッチ）** - 対象テーブル: 2個 ✅
   - MstPvp, MstPvpI18n

8. **イベント基本設定** - 対象テーブル: 3個 ✅
   - MstEvent, MstEventI18n, MstEventBonusUnit

9. **ショップ・パック** - 対象テーブル: 7個 ✅
   - MstStoreProduct, OprProduct, MstPack, MstPackI18n, MstPackContent等

10. **原画・エンブレム** - 対象テーブル: 7個 ✅
    - MstArtwork, MstArtworkFragment, MstArtworkFragmentPosition, MstEmblem等

### 優先度低（完成済み）

11. **敵・自動行動** - 対象テーブル: 5個 ✅
    - MstEnemyCharacter, MstEnemyStageParameter, MstAutoPlayerSequence等

12. **インゲーム（マンガアニメーション含む）** - 対象テーブル: 7個 ✅
    - MstInGame, MstInGameI18n, MstPage, MstKomaLine, MstMangaAnimation等

---

## トラブルシューティング

### Q1: 設計書のパスが見つからない

**原因**: specs CSVに記載されたパスが古い、または設計書が移動された

**対処法**:
1. Googleドライブで設計書を検索
2. 最新のパスをspecs CSVに記載
3. 設計書をローカルにダウンロード

### Q2: ID採番ルールが分からない

**原因**: 新しいシリーズやコンテンツタイプのID体系が未定義

**対処法**:
1. `analysis/output-masterdata-analysis.md`で既存のID体系を確認
2. GLOW_ID管理シートを参照
3. 開発チームに確認

### Q3: enum値が不明

**原因**: 手順書に記載されていないenum値が必要

**対処法**:
1. `projects/glow-server/api/database/schema/exports/master_tables_schema.json`でDBスキーマを確認
2. 既存のマスタデータCSVから実例を検索
3. 手順書を更新

### Q4: 推測値レポートに多数の項目がある

**原因**: 設計書に記載が不足している

**対処法**:
1. レポートの「確認事項」を企画チームに確認
2. 設計書を更新
3. 再度マスタデータを生成

### Q5: テーブル間のリレーションエラーが発生する

**原因**: 外部キーの整合性が取れていない

**対処法**:
1. 手順書の「データ整合性のチェック」セクションを確認
2. 親テーブルのIDが存在するか確認
3. 採番ルールが一貫しているか確認

---

## 参考資料

### 分析結果
- [設計書分析](analysis/input-documents-analysis.md) - 15件の設計書の詳細分析
- [マスタデータ分析](analysis/output-masterdata-analysis.md) - 79テーブルの分析
- [手順書テンプレート](analysis/reference-manual-structure.md) - 手順書の構成
- [マッピング](analysis/input-output-mapping.md) - 設計書↔テーブルの対応関係

### 完成した手順書（全12グループ）
1. [ヒーロー](manuals/hero/) - 13テーブル
2. [クエスト・ステージ](manuals/quest-stage/) - 10テーブル
3. [降臨バトル](manuals/advent-battle/) - 7テーブル
4. [ミッション](manuals/mission/) - 8テーブル
5. [ガチャ](manuals/gacha/) - 6テーブル
6. [アイテム・報酬](manuals/item-reward/) - 3テーブル
7. [PVP](manuals/pvp/) - 2テーブル
8. [イベント基本設定](manuals/event-basic/) - 3テーブル
9. [ショップ・パック](manuals/shop-pack/) - 7テーブル
10. [原画・エンブレム](manuals/artwork-emblem/) - 7テーブル
11. [敵・自動行動](manuals/enemy-autoplayer/) - 5テーブル
12. [インゲーム](manuals/ingame/) - 7テーブル（マンガアニメーション含む）

### その他
- [DBスキーマ](../../../../projects/glow-server/api/database/schema/exports/master_tables_schema.json)
- [既存の検証スキル](../../../../.claude/skills/masterdata-csv-validator/)

---

## システム構築の経緯

### 分析フェーズ（完了）

1. **input-analyzer**: 設計書15件を分析
   - 機能カテゴリ別サマリ
   - ID体系の確認
   - 階層構造の理解

2. **output-analyzer**: マスタデータ79テーブルを分析
   - テーブル別詳細分析
   - プレフィックス別グループ化
   - 採番ルールの推測

3. **reference-analyzer**: 参考手順書の構造分析
   - 手順書テンプレートの抽出
   - enum値一覧の整理方法
   - データ整合性チェックの構造

4. **mapping-analyzer**: インプット→アウトプットのマッピング
   - 設計書別マッピング（15件）
   - テーブル別逆引きマッピング（79個）
   - マッピングパターン分析

### 手順書作成フェーズ（完了）

5. **manual-writers**: 全12グループの手順書を作成
   - hero-manual-writer: ヒーロー手順書（13テーブル）
   - quest-stage-manual-writer: クエスト・ステージ手順書（10テーブル）
   - advent-battle-manual-writer: 降臨バトル手順書（7テーブル）
   - mission-manual-writer: ミッション手順書（8テーブル）
   - gacha-manual-writer: ガチャ手順書（6テーブル）
   - item-reward-manual-writer: アイテム・報酬手順書（3テーブル）
   - pvp-manual-writer: PVP手順書（2テーブル）
   - event-basic-manual-writer: イベント基本設定手順書（3テーブル）
   - shop-pack-manual-writer: ショップ・パック手順書（7テーブル）
   - artwork-emblem-manual-writer: 原画・エンブレム手順書（7テーブル）
   - enemy-autoplayer-manual-writer: 敵・自動行動手順書（5テーブル）
   - ingame-manual-writer: インゲーム手順書（7テーブル、マンガアニメーション含む）

6. **doc-writer**: 全体ドキュメント（本ファイル）を作成・更新

---

## 貢献者

このシステムは、Claude Code agent teamによって構築されました:

### 分析フェーズチーム
- **input-analyzer**: 設計書分析担当
- **output-analyzer**: マスタデータ分析担当
- **reference-analyzer**: 手順書構造分析担当
- **mapping-analyzer**: マッピング分析担当
- **team-lead**: 全体調整担当

### 手順書作成フェーズチーム
- **hero-manual-writer**: ヒーロー手順書作成担当
- **quest-stage-manual-writer**: クエスト・ステージ手順書作成担当
- **advent-battle-manual-writer**: 降臨バトル手順書作成担当
- **mission-manual-writer**: ミッション手順書作成担当
- **gacha-manual-writer**: ガチャ手順書作成担当
- **item-reward-manual-writer**: アイテム・報酬手順書作成担当
- **pvp-manual-writer**: PVP手順書作成担当
- **event-basic-manual-writer**: イベント基本設定手順書作成担当
- **shop-pack-manual-writer**: ショップ・パック手順書作成担当
- **artwork-emblem-manual-writer**: 原画・エンブレム手順書作成担当
- **enemy-autoplayer-manual-writer**: 敵・自動行動手順書作成担当
- **ingame-manual-writer**: インゲーム手順書作成担当
- **doc-writer**: 全体ドキュメント作成・更新担当

---

## ライセンス

内部プロジェクト専用

---

## 更新履歴

- 2026-02-10: v2.0リリース - 全機能グループ完成
  - 全12グループの手順書完成（24ファイル作成）
  - 全79テーブルを100%カバー
  - README.md更新（完成状態を反映）

- 2026-02-10: 初版作成（v1.0）
  - ヒーロー手順書完成
  - 全体ドキュメント作成
  - 分析結果の統合
