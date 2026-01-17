手順書(降臨バトルミッション_マスタデータ設定手順書.md)に従って
仕様書(添付したcsvファイル)から
降臨バトルミッションのマスタデータを作成してください。

## release_key

`{release_keyを指定、未指定の場合はファイル名から抽出します}`

## ID採番の開始番号

| カラム | 開始番号 |
|--------|----------|
| MstMissionLimitedTerm.id | limited_term_{ここから開始} |
| MstMissionLimitedTerm.progress_group_key | group{ここから開始} |
| MstMissionReward.id | mission_reward_{ここから開始} |

## 出力形式

- CSV形式での表示は不要
- Markdown表形式で出力（レイアウト崩れがないよう、スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式で）
