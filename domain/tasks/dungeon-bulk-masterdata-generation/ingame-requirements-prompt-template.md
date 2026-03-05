# dungeon インゲーム要件テキスト 生成プロンプト

`/masterdata-ingame-creator` に渡す「インゲーム要件テキスト」を作成するためのプロンプト。
`{series_id}` を対象作品のIDに置き換えて使う。

---

## プロンプト

```
限界チャレンジ（dungeon）のインゲームマスタデータ作成に使う「インゲーム要件テキスト」を生成してください。
マスタデータ（CSV）の作成はまだ行いません。

## 参考・仕様ドキュメント

インゲーム要件テキストの出力フォーマット参考：
@domain/tasks/dungeon-bulk-masterdata-generation/参考例_インゲーム要件テキスト.md

対象作品のインゲームパターン分析（敵ID・アセットキー・シーケンスパターン等）：
@domain/tasks/dungeon-bulk-masterdata-generation/knowledge/ingame-patterns/{series_id}-pattern-analysis.md

限界チャレンジのバトルシステム・マスタデータ設計仕様：
@domain/tasks/dungeon-bulk-masterdata-generation/knowledge/dungeon-ingame-basic-requirements.md

## 作成対象

- 作品: {series_id}
- ブロック: 通常ブロック（dungeon_normal）・ボスブロック（dungeon_boss）各1つ

## 出力形式

通常ブロック・ボスブロックの順に、各ブロックを以下の2パート構成で出力すること。

**パート1: 要件テキスト（日本語散文）**
参考例と同じスタイルで記述し、以下の内容を必ず含めること：
- インゲームID・ブロック種別
- 敵ゲートHP（dungeon仕様の固定値）
- BGMアセットキー・ループ背景アセットキー
- コマ行数（dungeon仕様の固定値）・コマ効果の有無
- 登場する敵のキャラID・属性・HP・攻撃力・移動速度・役割
- シーケンス構成（トリガー種別・グループ切替の有無・行数目安）
- バトルヒント・ステージ説明文のイメージ

**パート2: 設計メモ（表）**
パート1の主要パラメータを一覧表にまとめること。

## 設計の方針

- パターン分析に記載された敵ID・アセットキーを正確に使用すること
- dungeon仕様の固定値（HP・コマ行数等）を必ず守ること
- 作品の世界観・キャラクターの個性が伝わる設計にすること

## 禁止事項

以下は絶対に参照しないこと：
- domain/tasks/masterdata-entry
```

---

## 生成後の保存

生成されたら以下のプロンプトで保存を依頼する。

```
outputs/{series_id}/boss, normal にmdファイルとして保存しておいて
```

保存先:
```
outputs/{series_id}/normal/dungeon_{series_id}_normal_00001.md
outputs/{series_id}/boss/dungeon_{series_id}_boss_00001.md
```

---

## spy での実施記録

- 生成ファイル: `outputs/spy/normal/dungeon_spy_normal_00001.md`, `outputs/spy/boss/dungeon_spy_boss_00001.md`
- 参照した主な情報: 雑魚敵ID（`enemy_spy_00001`, `enemy_spy_00101`）、ロイドのキャラID（`chara_spy_00101`）、BGM（`SSE_SBG_003_002`）、背景（`spy_00005`）
