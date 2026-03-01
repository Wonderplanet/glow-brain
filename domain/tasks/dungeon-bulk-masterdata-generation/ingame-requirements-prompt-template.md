# インゲーム要件テキスト 作成プロンプトテンプレート

このファイルは、限界チャレンジ（dungeon）のインゲーム要件テキストを作成する際のプロンプト参考例です。
`/masterdata-ingame-creator` スキルへの入力として使うインゲーム要件テキストを生成することが目的です。

---

## 参照させるファイル（必須）

プロンプトに以下を `@ファイルパス` で添付してください。

| 役割 | ファイルパス |
|------|------------|
| インゲーム要件テキストの参考例 | `domain/tasks/dungeon-bulk-masterdata-generation/参考例_インゲーム要件テキスト.md` |
| 対象作品のパターン分析 | `domain/tasks/dungeon-bulk-masterdata-generation/knowledge/ingame-patterns/{series_id}-pattern-analysis.md` |
| dungeon基本要件（仕様） | `domain/tasks/dungeon-bulk-masterdata-generation/knowledge/dungeon-ingame-basic-requirements.md` |

---

## プロンプト本文（テンプレート）

```
限界チャレンジ(dungeon)のインゲームマスタデータを作成するための
インゲーム要件テキストを作りたい。（マスタデータ作成はまだやりません。）

インゲーム要件テキストの参考例：
@参考例_インゲーム要件テキスト.md

このような、インゲーム要件テキストをインプットとして、masterdata-ingame-creatorスキルを使って、
インゲームマスタデータを作成することを考えています。

インゲーム要件については、作品ごとに各種要素で特徴があります。
その特徴を既存データや下記のドキュメントを参考に、しっかり反映しつつ、
作品ごとの特徴を捉えた、楽しいインゲームデータを作れるようにする必要があります。
@knowledge/ingame-patterns/{series_id}-pattern-analysis.md

限界チャレンジにおけるインゲームの基本要件についてはこちらです
@knowledge/dungeon-ingame-basic-requirements.md

上記のための「インゲーム要件テキスト」を
まずは {series_id} で2ブロック(通常ブロック、ボスブロック)で、それぞれ1つずつを作ってください。

禁止事項：
- 下記は絶対に見ないでください。参考にしないでください。禁止です。
    - /Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-supachababy/domain/tasks/masterdata-entry
```

---

## 生成後の保存先

要件テキスト生成後は以下に保存してください。

```
outputs/{series_id}/normal/dungeon_{series_id}_normal_00001.md
outputs/{series_id}/boss/dungeon_{series_id}_boss_00001.md
```

保存を依頼する追加プロンプト例：
```
outputs/{series_id}/boss, normal ... にmdファイルとして保存しておいて
```

---

## SPY×FAMILYでの実施例（spy）

### 生成された要件テキストの保存先

- `outputs/spy/normal/dungeon_spy_normal_00001.md`
- `outputs/spy/boss/dungeon_spy_boss_00001.md`

### 生成時にClaudeが参照・活用した情報

| 参照情報 | 使用内容 |
|---------|---------|
| `spy-pattern-analysis.md` | 雑魚敵ID（`enemy_spy_00001`, `enemy_spy_00101`）、ロイドのキャラID（`chara_spy_00101`）、BGM/背景アセットキー（`SSE_SBG_003_002`, `spy_00005`）の確認 |
| `dungeon-ingame-basic-requirements.md` | normalブロック（HP=100固定、3行コマ、ElapsedTimeシーケンス）、bossブロック（HP=1000固定、1行コマ、InitialSummonボス配置、ゲートダメージ無効）の仕様確認 |
| `参考例_インゲーム要件テキスト.md` | テキストの文体・粒度・構成フォーマットの参考 |

---

## 注意事項

- 禁止ディレクトリ（`domain/tasks/masterdata-entry`）は絶対に参照しないこと
- インゲーム要件テキスト作成時点ではCSV生成は行わない（次のステップ）
- 次のステップは `/masterdata-ingame-creator` スキルで要件テキストをインプットにCSVを生成する
