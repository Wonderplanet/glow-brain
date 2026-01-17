手順書(降臨バトルミッション_マスタデータ設定手順書.md)に従って
仕様書(添付したcsvファイル)から
降臨バトルミッションのマスタデータを作成してください。

## 入力パラメータ（要入力）

- release_key: {release_keyを指定}
- 作品ID: {作品IDを指定}
- MstMissionLimitedTerm.id: limited_term_{連番の開始番号}
- MstMissionLimitedTerm.progress_group_key: group{連番の開始番号}
- MstMissionReward.id: ission_reward_{連番の開始番号}

## 出力形式

- 手順書の「データ整合性のチェック」を満たしたデータになっていることを必ずチェックし、問題があれば修正完了してから作業を完了し出力する
- CSV形式での表示は不要
- Markdown表形式で出力（レイアウト崩れがないよう、スプレッドシートへのエクスポート・コピーボタンが正常に表示される形式で）
