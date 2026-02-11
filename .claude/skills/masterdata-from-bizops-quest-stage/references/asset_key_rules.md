# アセットキー生成ルール

## イベントID → アセットキープレフィックス
- `event_jig_00001` → `jig_`
- `event_sur1_00001` → `sur1_`
- `event_cal1_00001` → `cal1_`
- `event_adv1_00001` → `adv1_`

## クエストタイプ → 背景アセットキー
- `charaget01`（キャラ取得クエスト1） → `jig_00003`
- `charaget02`（キャラ取得クエスト2） → `jig_00002`
- `1day`（1日1回クエスト） → `jig_00001`
- `story`（ストーリークエスト） → `jig_00001`
- `challenge`（チャレンジクエスト） → `jig_00002`
- `high_difficulty`（高難易度クエスト） → `jig_00003`

## 実装例（TypeScript擬似コード）

```typescript
function generateAssetKey(eventId: string, questType?: string): string {
  // イベントIDからプレフィックス抽出
  const match = eventId.match(/event_(\w+)_/)
  if (!match) throw new Error(`Invalid event ID: ${eventId}`)

  const prefix = match[1] // "jig", "sur1" など

  if (questType) {
    // クエストタイプ別の背景アセットキー
    const QUEST_ASSET_MAP = {
      "charaget01": `${prefix}_00003`,
      "charaget02": `${prefix}_00002`,
      "1day": `${prefix}_00001`,
      "story": `${prefix}_00001`,
      "challenge": `${prefix}_00002`,
      "high_difficulty": `${prefix}_00003`
    }
    return QUEST_ASSET_MAP[questType] || `${prefix}_00001`
  }

  return `${prefix}_00001`
}
```

## アセットキー命名パターン

### 背景画像アセット
- 形式: `{prefix}_{連番5桁}`
- 例: `jig_00001`, `sur1_00015`

### キャラクターアセット
- 形式: `chara_{キャラID}`
- 例: `chara_001`, `chara_002`

### エフェクトアセット
- 形式: `effect_{効果種別}_{連番}`
- 例: `effect_fire_001`, `effect_water_002`

## 注意事項

1. **イベントIDとの整合性**: アセットキーのプレフィックスは必ずイベントIDから抽出する
2. **デフォルト値**: 不明な場合は`{prefix}_00001`を使用
3. **連番のゼロパディング**: 連番は必ず5桁のゼロパディング（例: 00001, 00015）
4. **大文字小文字**: アセットキーはすべて小文字を使用

## 過去データから学んだパターン

### イベント種別ごとの典型的なアセットキー

| イベント種別 | プレフィックス | 主要背景アセット |
|------------|--------------|----------------|
| ジグソーイベント | `jig_` | `jig_00001`, `jig_00002`, `jig_00003` |
| サバイバルイベント | `sur1_` | `sur1_00001`, `sur1_00015` |
| カレンダーイベント | `cal1_` | `cal1_00001`, `cal1_00002` |
| アドベントバトル | `adv1_` | `adv1_00001`, `adv1_00010` |

### クエスト難易度とアセットキーの関連

- 低難易度（ステージ1-5）: `{prefix}_00001`
- 中難易度（ステージ6-10）: `{prefix}_00002`
- 高難易度（ステージ11+）: `{prefix}_00003`
