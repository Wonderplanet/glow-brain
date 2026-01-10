# テーブル命名規則

## 基本ルール

### 1. DB接頭辞は必須

すべてのテーブル名には、DB接続に対応する接頭辞を付けてください。

| DB接続 | 接頭辞 | 例 |
|--------|--------|-----|
| mst | `mst_`, `opr_` | `mst_units`, `opr_gachas` |
| mng | `mng_` | `mng_messages`, `mng_client_versions` |
| usr | `usr_` | `usr_user_profiles`, `usr_user_devices` |
| log | `log_` | `log_pvp_actions`, `log_logins` |
| sys | `sys_` | `sys_pvp_seasons`, `sys_maintenance` |
| admin | `adm_` | `adm_user_ban_histories`, `adm_informations` |

### 2. 複数形を使用

テーブル名は基本的に複数形を使用してください（Laravelの規約に準拠）。

**✅ 正しい例:**
```
mst_units
usr_user_profiles
log_pvp_actions
mng_messages
adm_informations
```

**❌ 間違った例:**
```
mst_unit           # 単数形は避ける
usr_user_profile   # 単数形は避ける
log_pvp_action     # 単数形は避ける
```

### 3. 例外: i18n系テーブル

多言語対応テーブルは`_i18n`で終わり、**複数形にしません**。

**✅ 正しい例:**
```
mng_messages_i18n
opr_gachas_i18n
mst_units_i18n
adm_informations_i18n
```

**❌ 間違った例:**
```
mng_messages_i18ns     # i18nを複数形にしない
opr_gachas_i18ns       # i18nを複数形にしない
mst_units_i18ns        # i18nを複数形にしない
```

**理由**: `i18n`は"internationalization"の略で、それ自体が複数の言語を表す概念のため、複数形にしません。

## 命名パターン

### 単純なテーブル

```
{db接頭辞}_{エンティティ名の複数形}

例:
mst_units
usr_users
log_logins
adm_banners
```

### 関連テーブル・中間テーブル

```
{db接頭辞}_{親エンティティ名}_{子エンティティ名の複数形}

例:
mst_unit_skills
usr_user_devices
log_stage_actions
adm_user_ban_operate_histories
```

### 多言語テーブル

```
{db接頭辞}_{エンティティ名の複数形}_i18n

例:
mng_messages_i18n
opr_gachas_i18n
mst_units_i18n
```

### 集約・サマリーテーブル

```
{db接頭辞}_{エンティティ名}_{種類}_summaries

例:
usr_currency_summaries
usr_item_summaries
```

### 履歴・ログテーブル

```
{db接頭辞}_{エンティティ名の複数形}_histories
または
log_{アクション名の複数形}

例:
adm_user_ban_operate_histories
usr_store_product_histories
log_currency_paids
log_pvp_actions
```

## 複合語の扱い

### アンダースコアで区切る

複数の単語を組み合わせる場合は、アンダースコアで区切ってください。

**✅ 正しい例:**
```
usr_user_profiles          # user + profiles
mst_unit_fragment_converts # unit + fragment + converts
log_stage_actions          # stage + actions
adm_user_ban_operate_histories # user + ban + operate + histories
```

**❌ 間違った例:**
```
usr_userprofiles           # 単語が繋がっている
mst_unitfragmentconverts   # 単語が繋がっている
```

### 長すぎる名前は適度に省略

MySQLのテーブル名は64文字まで、インデックス名も制限があるため、過度に長い名前は避けてください。

**妥当な例:**
```
adm_user_ban_operate_histories (30文字)
usr_store_product_histories (27文字)
```

**長すぎる場合の対処:**
```
# 適度に省略
adm_user_ban_op_histories     # operate → op
usr_currency_revert_histories # revert_history → histories
```

## 実例集

### mst接続のテーブル例

```
mst_units                    # ユニットマスター
mst_items                    # アイテムマスター
mst_stages                   # ステージマスター
mst_quests                   # クエストマスター
mst_unit_skills              # ユニットスキル
mst_stage_rewards            # ステージ報酬
opr_gachas                   # ガチャ運営
opr_products                 # 商品運営
opr_gacha_prizes             # ガチャ景品
```

### mng接続のテーブル例

```
mng_messages                 # メッセージ管理
mng_messages_i18n            # メッセージ多言語
mng_client_versions          # クライアントバージョン管理
mng_content_closes           # コンテンツ停止管理
mng_deleted_my_ids           # 削除済みMy ID管理
```

### usr接続のテーブル例

```
usr_user_profiles            # ユーザープロフィール
usr_user_devices             # ユーザーデバイス
usr_currency_summaries       # 通貨集約
usr_currency_frees           # 無償通貨詳細
usr_currency_paids           # 有償通貨詳細
usr_store_allowances         # ショップ購入許可
usr_store_product_histories  # ショップ購入履歴
usr_pvp_sessions             # PVPセッション
```

### log接続のテーブル例

```
log_logins                   # ログインログ
log_pvp_actions              # PVPアクションログ
log_stage_actions            # ステージアクションログ
log_currency_paids           # 有償通貨ログ
log_currency_frees           # 無償通貨ログ
log_currency_cashes          # 二次通貨ログ
log_stores                   # ショップ購入ログ
log_banks                    # バンクログ
```

### sys接続のテーブル例

```
sys_pvp_seasons              # PVPシーズン管理
sys_maintenance              # メンテナンス管理
sys_feature_flags            # 機能フラグ
```

### admin接続のテーブル例

```
adm_users                             # 管理ユーザー
adm_informations                      # お知らせ管理
adm_banners                           # バナー管理
adm_user_ban_operate_histories        # ユーザーBAN操作履歴
adm_user_deletion_operate_histories   # ユーザー削除操作履歴
adm_foreign_currency_rates            # 為替レート
adm_foreign_currency_daily_rates      # 為替レート（日次）
adm_gacha_simulation_logs             # ガチャシミュレーションログ
adm_promotion_tags                    # プロモーションタグ
```

## よくある間違い

### 1. 接頭辞の付け忘れ

```
❌ users                    # 接頭辞がない
✅ usr_users                # 正しい

❌ messages                 # 接頭辞がない
✅ mng_messages             # 正しい
```

### 2. 単数形の使用

```
❌ mst_unit                 # 単数形
✅ mst_units                # 複数形

❌ log_login                # 単数形
✅ log_logins               # 複数形
```

### 3. i18nの複数形化

```
❌ mng_messages_i18ns       # i18nを複数形にしている
✅ mng_messages_i18n        # i18nは複数形にしない

❌ opr_gachas_i18ns         # i18nを複数形にしている
✅ opr_gachas_i18n          # i18nは複数形にしない
```

### 4. 不適切な接頭辞

```
❌ mst_usr_profiles         # mstなのにusr_が含まれている
✅ usr_user_profiles        # usr接続なのでusr_

❌ usr_mst_items            # usrなのにmst_が含まれている
✅ mst_items                # mst接続なのでmst_
```

## チェックリスト

新しいテーブルを作成する際は、以下を確認してください：

- [ ] DB接頭辞が正しく付いているか（mst_/mng_/usr_/log_/sys_/adm_）
- [ ] 複数形を使用しているか（i18n系を除く）
- [ ] i18n系テーブルは`_i18n`で終わっているか（複数形にしていないか）
- [ ] 複合語は適切にアンダースコアで区切られているか
- [ ] テーブル名の長さは妥当か（64文字以内）
- [ ] 命名が一貫性を持っているか（既存テーブルと比較）

## 命名時の参考コマンド

既存のテーブル名を確認して、命名規則の一貫性を保ちましょう。

```bash
# mst接続のテーブル一覧
ls api/database/migrations/mst/ | grep -o "create_[^_]*_[^.]*" | sort

# mng接続のテーブル一覧
ls api/database/migrations/mng/ | grep -o "create_[^_]*_[^.]*" | sort

# usr/log/sys接続のテーブル一覧
ls api/database/migrations/ | grep -o "create_[^_]*_[^.]*" | sort

# admin接続のテーブル一覧
ls admin/database/migrations/ | grep -o "create_[^_]*_[^.]*" | sort
```
