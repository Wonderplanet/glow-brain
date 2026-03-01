# dungeon 一括生成 実行計画

> 作成日: 2026-03-01

---

## 概要

`/masterdata-ingame-creator` スキルを使い、15作品 × 2ブロック（normal/boss）= **30ブロック** を
1ブロックずつ独立したサブフォルダに出力し、並列作業で一括生成する。

---

## ディレクトリ構造

各ブロックをインゲームIDごとに **完全に分離** する。

```
outputs/
  {series_id}/
    dungeon_{series_id}_normal_00001/    ← normalブロック専用
      design.md
      dungeon_{series_id}_normal_00001.md
      generated/
        MstEnemyStageParameter.csv
        MstEnemyOutpost.csv
        MstPage.csv
        MstKomaLine.csv
        MstAutoPlayerSequence.csv
        MstInGame.csv
    dungeon_{series_id}_boss_00001/      ← bossブロック専用
      design.md
      dungeon_{series_id}_boss_00001.md
      generated/
        MstEnemyStageParameter.csv  ← ボスのパラメータのみ（護衛雑魚はnormal側から流用）
        MstEnemyOutpost.csv
        MstPage.csv
        MstKomaLine.csv
        MstAutoPlayerSequence.csv
        MstInGame.csv
```

> **重要**: normalとbossは必ず別フォルダ。同じ `generated/` に混在させない。

---

## スキル実行時の指示（共通）

各ブロックのスキル実行時に、出力先を必ず明示的に指定する。

```
出力先: domain/tasks/dungeon-bulk-masterdata-generation/outputs/{series_id}/dungeon_{series_id}_{block}_00001/
```

---

## spyの処置（既存データの整理）

spy は一部生成済みだが、ディレクトリ構造が旧形式のため整理が必要。

| ブロック | 現状 | 対応 |
|---------|------|------|
| dungeon_spy_normal_00001 | **未生成**（masterdata-entry側にデータあり） | 新規生成（本計画で実施） |
| dungeon_spy_boss_00001 | **生成済み**（`outputs/spy/generated/` に混在） | フォルダ移動して整理 |

spy boss の移動先: `outputs/spy/dungeon_spy_boss_00001/`

---

## 全30ブロック 実行チェックリスト

### 要件テキスト参照先

`domain/tasks/dungeon-bulk-masterdata-generation/ingame-requirements.md`

---

### spy｜SPY×FAMILY

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_spy_normal_00001` | `enemy_spy_00001` | `enemy_spy_00101` | なし | ⬜ 未着手 |
| boss | `dungeon_spy_boss_00001` | — | — | `chara_spy_00101` | ✅ 生成済み（移動要） |

---

### chi｜チェンソーマン

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_chi_normal_00001` | `enemy_chi_00101` | `enemy_chi_00201` | なし | ⬜ 未着手 |
| boss | `dungeon_chi_boss_00001` | — | — | `chara_chi_00002` | ⬜ 未着手 |

---

### dan｜ダンダダン

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_dan_normal_00001` | `enemy_dan_00001` | `enemy_dan_00101` | なし | ⬜ 未着手 |
| boss | `dungeon_dan_boss_00001` | — | — | `chara_dan_00002` | ⬜ 未着手 |

---

### gom｜姫様"拷問"の時間です

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_gom_normal_00001` | `enemy_gom_00402` | `enemy_gom_00801` | なし | ⬜ 未着手 |
| boss | `dungeon_gom_boss_00001` | — | — | `chara_gom_00001` | ⬜ 未着手 |

---

### hut｜ふつうの軽音部

> ⚠️ 専用雑魚なし → glo汎用敵を使用

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_hut_normal_00001` | `enemy_glo_00001` | `enemy_glo_00002` | なし | ⬜ 未着手 |
| boss | `dungeon_hut_boss_00001` | — | — | `chara_hut_00001` | ⬜ 未着手 |

---

### jig｜地獄楽

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_jig_normal_00001` | `enemy_jig_00401` | `enemy_jig_00001` | なし | ⬜ 未着手 |
| boss | `dungeon_jig_boss_00001` | — | — | `chara_jig_00001` | ⬜ 未着手 |

---

### kai｜怪獣８号

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_kai_normal_00001` | `enemy_kai_00101` | `enemy_kai_00301` | なし | ⬜ 未着手 |
| boss | `dungeon_kai_boss_00001` | — | — | `chara_kai_00002` | ⬜ 未着手 |

---

### kim｜君のことが大大大大大好きな100人の彼女

> ⚠️ 専用雑魚なし → glo汎用敵を使用

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_kim_normal_00001` | `enemy_glo_00001` | `enemy_glo_00002` | なし | ⬜ 未着手 |
| boss | `dungeon_kim_boss_00001` | — | — | `chara_kim_00001` | ⬜ 未着手 |

---

### mag｜株式会社マジルミエ

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_mag_normal_00001` | `enemy_mag_00301` | `enemy_mag_00101` | なし | ⬜ 未着手 |
| boss | `dungeon_mag_boss_00001` | — | — | `chara_mag_00001` | ⬜ 未着手 |

---

### osh｜【推しの子】

> ⚠️ 専用雑魚なし → glo汎用敵を使用

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_osh_normal_00001` | `enemy_glo_00001` | `enemy_glo_00002` | なし | ⬜ 未着手 |
| boss | `dungeon_osh_boss_00001` | — | — | `chara_osh_00001` | ⬜ 未着手 |

---

### sum｜サマータイムレンダ

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_sum_normal_00001` | `enemy_sum_00001` | `chara_sum_00201` | なし | ⬜ 未着手 |
| boss | `dungeon_sum_boss_00001` | — | — | `chara_sum_00101` | ⬜ 未着手 |

---

### sur｜魔都精兵のスレイブ

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_sur_normal_00001` | `enemy_sur_00101` | なし（1体のみ） | なし | ⬜ 未着手 |
| boss | `dungeon_sur_boss_00001` | — | — | `chara_sur_00101` | ⬜ 未着手 |

---

### tak｜タコピーの原罪

> ⚠️ 専用雑魚なし → glo汎用敵を使用

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_tak_normal_00001` | `enemy_glo_00001` | `enemy_glo_00002` | なし | ⬜ 未着手 |
| boss | `dungeon_tak_boss_00001` | — | — | `chara_tak_00001` | ⬜ 未着手 |

---

### you｜幼稚園WARS

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_you_normal_00001` | `enemy_you_00001` | `enemy_you_00101` | なし | ⬜ 未着手 |
| boss | `dungeon_you_boss_00001` | — | — | `chara_you_00001` | ⬜ 未着手 |

---

### yuw｜2.5次元の誘惑

> ⚠️ 専用雑魚なし → glo汎用敵を使用

| ブロック | インゲームID | 雑魚メイン | 雑魚サブ | ボス | 状態 |
|---------|------------|----------|--------|------|------|
| normal | `dungeon_yuw_normal_00001` | `enemy_glo_00001` | `enemy_glo_00002` | なし | ⬜ 未着手 |
| boss | `dungeon_yuw_boss_00001` | — | — | `chara_yuw_00301` | ⬜ 未着手 |

---

## 並列実行のルール

### 同時に走らせてよい組み合わせ

- **作品が異なるブロックはすべて並列OK**
  例: `dungeon_chi_normal_00001` と `dungeon_dan_boss_00001` は同時実行可能

- **同一作品のnormalとbossも並列OK**
  出力先が `dungeon_{series}_normal_00001/` と `dungeon_{series}_boss_00001/` で分離されているため競合しない

### 制約

- **bossブロックはnormalブロックの雑魚パラメータIDを護衛として参照する**
  → bossの `MstAutoPlayerSequence` 内で normalブロックの `MstEnemyStageParameter.id` を使う
  → CSVの生成自体はbossが先でも問題ないが、**参照するIDを事前に確認しておく**こと

  normalブロックの雑魚パラメータIDの命名規則:
  ```
  e_{enemy_id}_{series_id}_dungeon_Normal_{color}
  例: e_spy_00001_spy_dungeon_Normal_Colorless
  ```

---

## 進捗管理

生成完了したら状態列を更新する。

| 記号 | 意味 |
|------|------|
| ⬜ | 未着手 |
| 🔄 | 生成中 |
| ✅ | 生成完了 |
| ❌ | エラー・要修正 |
