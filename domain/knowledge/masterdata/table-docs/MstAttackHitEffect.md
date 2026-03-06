# MstAttackHitEffect 詳細説明

> CSVパス: `projects/glow-masterdata/MstAttackHitEffect.csv`

---

## 概要

ユニットの攻撃がヒットした際の演出（擬音語・サウンドエフェクト）設定を管理するマスタテーブル。
攻撃の種類ごとに、表示する擬音語アセット（最大3種）と通常SE・弱点攻撃SEのアセットキーを定義する。
インゲームバトルにおける視覚・聴覚演出の組み合わせを制御する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| id | varchar(255) | YES | レコードID（主キー） |
| onomatopoeia1_asset_key | varchar(255) | YES | 擬音語アセットキー1（必ず設定、デフォルト: 空文字） |
| onomatopoeia2_asset_key | varchar(255) | YES | 擬音語アセットキー2（デフォルト: 空文字） |
| onomatopoeia3_asset_key | varchar(255) | YES | 擬音語アセットキー3（デフォルト: 空文字） |
| sound_effect_asset_key | varchar(255) | YES | 通常攻撃時のSEアセットキー（デフォルト: 空文字） |
| killer_sound_effect_asset_key | varchar(255) | YES | 弱点攻撃時のSEアセットキー（デフォルト: 空文字） |

---

## 命名規則 / IDの生成ルール

IDは攻撃種別の略称と連番で構成される。

| パターン | 意味 |
|---|---|
| `dageki_{N}` | 打撃系攻撃（N: 連番） |
| `zangeki_{N}` | 斬撃系攻撃（N: 連番） |
| `shageki_{N}` | 射撃系攻撃（N: 連番） |

---

## 他テーブルとの連携

このテーブルは主に `mst_attacks` テーブルから参照され、インゲームの攻撃ヒット演出として使用される。アセットキーはAddressablesシステムでロードされるアセットを指定する。

| 参照元テーブル | 用途 |
|---|---|
| `mst_attacks` | 攻撃設定からヒットエフェクトIDを参照 |

---

## 実データ例

**例1: 打撃系攻撃1（dageki_1）**

| id | onomatopoeia1_asset_key | onomatopoeia2_asset_key | onomatopoeia3_asset_key | sound_effect_asset_key | killer_sound_effect_asset_key |
|---|---|---|---|---|---|
| dageki_1 | Do | Doka | Doka2 | SSE_051_004 | SSE_051_013 |

**例2: 斬撃系攻撃1（zangeki_1）**

| id | onomatopoeia1_asset_key | onomatopoeia2_asset_key | onomatopoeia3_asset_key | sound_effect_asset_key | killer_sound_effect_asset_key |
|---|---|---|---|---|---|
| zangeki_1 | Za | Zashu | Zuba | SSE_051_004 | SSE_051_014 |

通常SE（`sound_effect_asset_key`）は共通の値を使いつつ、弱点攻撃SE（`killer_sound_effect_asset_key`）で攻撃種別ごとに異なるSEを設定している。

---

## 設定時のポイント

1. `onomatopoeia1_asset_key` は必ず設定する（空文字は可だが演出なしになる）。
2. 擬音語アセットキー（1〜3）はインゲームでランダムまたは順番に表示される可能性があるため、攻撃の雰囲気に合わせて複数パターンを設定することが推奨される。
3. `sound_effect_asset_key` は通常ヒット時、`killer_sound_effect_asset_key` は弱点属性へのヒット時に再生される。
4. SEアセットキーは `SSE_` プレフィックスを持つ命名規則を使用している（例: `SSE_051_004`）。
5. 同じ通常SEを複数のヒットエフェクトで共用することが一般的（例: `SSE_051_004` は打撃・射撃共通）。
6. 弱点攻撃SEは攻撃種別ごとに異なるキーを設定することで差別化する。
7. 新たな攻撃種別を追加する場合はIDを `{種別}_{連番}` の形式で命名する。
