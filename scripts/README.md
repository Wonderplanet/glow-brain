# 運営仕様書検証ツール

## validate-bizops-specs（仮想ツール）

運営仕様書の必須項目をチェックする検証ツールの仕様です。

### 機能

- 運営仕様書の必須項目をチェック
- 不足している項目をエラーとして報告
- 推奨項目の有無を警告として報告

### 検証ルール（TypeScript擬似コード）

```typescript
/**
 * 運営仕様書の必須項目をチェック
 */
function validateBizOpsSpecs(specFile: string, specType: string): ValidationResult {
  const data = parseSpreadsheet(specFile)
  const errors: string[] = []

  const REQUIRED_FIELDS = {
    "hero": ["unit_id", "name", "rarity", "role", "ability_id", "attack_id"],
    "gacha": ["gacha_id", "name", "start_date", "end_date", "prize_list", "upper_count"],
    "mission": ["mission_id", "name", "condition", "reward_list"]
  }

  const requiredFields = REQUIRED_FIELDS[specType] || []

  for (const field of requiredFields) {
    if (!hasField(data, field)) {
      errors.push(`必須項目が不足: ${field}`)
    }
  }

  return {
    isValid: errors.length === 0,
    errors,
    warnings: []
  }
}
```

### 使用方法（将来実装時）

```bash
# ヒーロー設計書の検証
npm run validate-bizops-specs -- --file=ヒーロー基礎設計.xlsx --type=hero

# ガチャ設計書の検証
npm run validate-bizops-specs -- --file=ガチャ設計書.xlsx --type=gacha
```
