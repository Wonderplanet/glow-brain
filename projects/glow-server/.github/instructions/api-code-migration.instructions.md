---
applyTo: "api/database/migrations/**/*.php"
---

- DB接頭辞ごとに、`api/database/migrations/`直下（usr_*, log_*, mng_*, mst_* など）や、`mng/`, `mst/` サブディレクトリに分けて配置してください。
- テーブル作成・変更時は、**LaravelのSchemaビルダー**を使い、`Blueprint`型でカラム定義を行ってください。
- **時刻系カラム**は、`timestampTz`（タイムゾーン付き）を使うこと。`created_at`, `updated_at` には `timestampsTz()` を利用してください。
- 主キーやユニークキーは `$table->primary([...])` や `$table->unique([...])` で明示的に指定してください。
- **コメント**は `$table->comment('...')` で必ず付与し、カラムにも `$table->string(...)->comment('...')` のように記述してください。
- **インデックス**は `$table->index([...], 'index_name')` で明示的に付与してください。
- **接頭辞ごとの配置例**:
  - `usr_`系: `api/database/migrations/`直下
  - `log_`系: `api/database/migrations/`直下
  - `mng_`系: `api/database/migrations/mng/`
  - `mst_`系: `api/database/migrations/mst/`
  - `opr_`系: `api/database/migrations/mst/`
- **テーブル名の動的生成**が必要な場合は、`DBUtility::getTableName('...')` などのユーティリティを利用してください。
- **down()** では、`dropIfExists` や `dropColumn` で確実にロールバックできるようにしてください。
- **enumや型制約**は `$table->enum(...)` や `$table->unsignedBigInteger(...)` などで厳密に指定してください。
- **nullable** や **default値** も明示的に指定してください。
- **複数カラムのユニーク制約やインデックス**も積極的に利用してください。

---
