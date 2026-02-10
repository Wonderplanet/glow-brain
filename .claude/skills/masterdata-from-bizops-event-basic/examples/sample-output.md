# イベント基本設定 マスタデータ サンプル出力

## 概要

このファイルは、イベント基本設定スキル (masterdata-from-bizops-event-basic) を使用した際の実際の出力例を示します。

## サンプル1: 地獄楽いいジャン祭

### 入力パラメータ

```
release_key: 202601010
mst_series_id: jig
event_id: event_jig_00001
event_name: 地獄楽いいジャン祭
start_at: 2026-01-16 15:00:00
end_at: 2026-02-16 10:59:59
is_displayed_series_logo: 1
is_displayed_jump_plus: 1
```

### 出力

#### MstEvent シート

| ENABLE | id | mst_series_id | is_displayed_series_logo | is_displayed_jump_plus | start_at | end_at | asset_key | release_key |
|--------|----|--------------|-----------------------|----------------------|----------|--------|----------|-------------|
| e | event_jig_00001 | jig | 1 | 1 | 2026-01-16 15:00:00 | 2026-02-16 10:59:59 | event_jig_00001 | 202601010 |

#### MstEventI18n シート

| ENABLE | release_key | id | mst_event_id | language | name | balloon |
|--------|------------|----|--------------|----|------|---------|
| e | 202601010 | event_jig_00001_ja | event_jig_00001 | ja | 地獄楽いいジャン祭 | 地獄楽いいジャン祭\n開催中! |

#### MstHomeBanner シート

| ENABLE | id | destination | destination_path | asset_key | start_at | end_at | sort_order | release_key |
|--------|----|------------|----------------|----------|----------|--------|-----------|-------------|
| e | 23 | Event | event_jig_00001 | hometop_event_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 7 | 202601010 |
| e | 24 | Gacha | Pickup_jig_001 | hometop_gacha_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 6 | 202601010 |
| e | 25 | Gacha | Pickup_jig_002 | hometop_gacha_jig_00002 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 6 | 202601010 |

#### 推測値レポート

##### MstEventI18n.balloon
- **値**: 地獄楽いいジャン祭\n開催中!
- **理由**: 設計書に吹き出しテキストの記載がなかったため、イベント名を基に訴求力のあるテキストを推測
- **確認事項**: 吹き出しテキストが適切であることを確認し、必要に応じて調整してください

##### MstHomeBanner.id
- **値**: 23, 24, 25
- **理由**: 設計書にバナーIDの記載がなかったため、既存の最大IDから連番を推測
- **確認事項**: 既存のMstHomeBannerで使用されているIDと重複していないことを確認してください

##### MstHomeBanner.sort_order
- **値**: 7, 6, 6
- **理由**: 設計書に表示順序の記載がなかったため、標準的な値を設定
- **確認事項**: 他のバナーとの表示順序を確認し、必要に応じて調整してください

## サンプル2: 推しの子コラボイベント

### 入力パラメータ

```
release_key: 202602010
mst_series_id: osh
event_id: event_osh_00001
event_name: 推しの子コラボ
start_at: 2026-02-01 12:00:00
end_at: 2026-03-01 11:59:59
is_displayed_series_logo: 1
is_displayed_jump_plus: 1
```

### 出力

#### MstEvent シート

| ENABLE | id | mst_series_id | is_displayed_series_logo | is_displayed_jump_plus | start_at | end_at | asset_key | release_key |
|--------|----|--------------|-----------------------|----------------------|----------|--------|----------|-------------|
| e | event_osh_00001 | osh | 1 | 1 | 2026-02-01 12:00:00 | 2026-03-01 11:59:59 | event_osh_00001 | 202602010 |

#### MstEventI18n シート

| ENABLE | release_key | id | mst_event_id | language | name | balloon |
|--------|------------|----|--------------|----|------|---------|
| e | 202602010 | event_osh_00001_ja | event_osh_00001 | ja | 推しの子コラボ | 推しの子コラボ\n期間限定開催! |

#### MstHomeBanner シート

| ENABLE | id | destination | destination_path | asset_key | start_at | end_at | sort_order | release_key |
|--------|----|------------|----------------|----------|----------|--------|-----------|-------------|
| e | 30 | Event | event_osh_00001 | hometop_event_osh_00001 | 2026-02-01 12:00:00 | 2026-02-15 11:59:59 | 5 | 202602010 |

## サンプル3: 複数バナーの優先順位設定

### 入力パラメータ

```
release_key: 202601010
event_id: event_jig_00001
```

### 出力(MstHomeBannerのみ抜粋)

#### MstHomeBanner シート

| ENABLE | id | destination | destination_path | asset_key | start_at | end_at | sort_order | release_key |
|--------|----|------------|----------------|----------|----------|--------|-----------|-------------|
| e | 23 | Event | event_jig_00001 | hometop_event_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 7 | 202601010 |
| e | 24 | Gacha | Pickup_jig_001 | hometop_gacha_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 6 | 202601010 |
| e | 25 | Gacha | Pickup_jig_002 | hometop_gacha_jig_00002 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 6 | 202601010 |
| e | 26 | AdventBattle | advent_jig_00001 | hometop_advent_jig_00001 | 2026-01-16 15:00:00 | 2026-02-02 14:59:59 | 8 | 202601010 |

**設定のポイント**:
- ガチャAバナー(id=24): sort_order=6(最優先)
- ガチャBバナー(id=25): sort_order=6(ガチャAと同優先度)
- イベントバナー(id=23): sort_order=7
- 降臨バトルバナー(id=26): sort_order=8

## 重要なポイント

### ID採番ルール

1. **MstEvent.id**: `event_{series_id}_{連番5桁}`
2. **MstEventI18n.id**: `{event_id}_{language}`
3. **MstHomeBanner.id**: 数値の連番

### destination別の設定

1. **Event**: destination_pathにイベントID(`event_jig_00001`等)を設定
2. **Gacha**: destination_pathにガチャID(`Pickup_jig_001`等)を設定
3. **AdventBattle**: destination_pathに降臨バトルID(`advent_jig_00001`等)を設定

### バナー表示期間の注意

- バナー表示期間はイベント開催期間と同じか、それより短い期間を設定
- 例: イベント開催期間(2026-01-16 ~ 2026-02-16)、バナー表示期間(2026-01-16 ~ 2026-02-02)

### 推測値レポートの重要性

設計書に記載がない値を推測で決定した場合、必ず推測値レポートに記載してください。これにより、データインポートエラーや本番不具合のリスクを低減できます。
