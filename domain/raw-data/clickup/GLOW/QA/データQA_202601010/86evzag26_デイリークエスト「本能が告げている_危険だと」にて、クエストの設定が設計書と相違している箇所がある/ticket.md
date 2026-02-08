# デイリークエスト「本能が告げている 危険だと」にて、クエストの設定が設計書と相違している箇所がある

【発生症状】
デイリークエスト「本能が告げている 危険だと」1話にて、
クエストの設定が設計書「【1日1回】本能が告げている 危険だと」と相違しております。

▼相違箇所
・通常BGM
・報酬設計
・エネミー設定

▼相違箇所詳細
・通常BGM
設計書：SSE\_SBG\_003\_006
マスター：SSE\_SBG\_003\_001

・報酬設計
設計書
クリアコイン 1,000
FirstClear コイン 1500

マスター＞ステージ報酬
クリアコイン1500

過去の設計書ではドロップ区分ごとに記載する報酬に、ステージ報酬のクリアコインは記載されておりませんでした。

・エネミー設定
「がらんの画眉丸」のロール設定

設計書：Technical
実機：Attack

【再現手順】
1.ユーザーサーバー時間変更で下記の期間内に設定する。
2026-01-16 15:00:00～2026-02-02 03:59:59
2.イベント＞地獄楽いいジャン祭＞デイリークエスト「本能が告げている 危険だと」の各箇所を確認する。

【補足】
・管理ツール
\[quest\_event\_jig1\_1day\] 本能が告げている 危険だと
[https://admin.dev-qa.glow.nappers.jp/admin/quest-detail?questId=quest\_event\_jig1\_1day](https://admin.dev-qa.glow.nappers.jp/admin/quest-detail?questId=quest_event_jig1_1day)

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa
検証ビルド：iOS #22
　　　　　　AOS #22
検証端末：iPhone13(iOS:17.0.3)
　　　　　Pixel9(AOS:14.0.0)

【ユーザー情報】
ユーザーID：A2784451496
　　　　　　A7907461105
ユーザー名：C9im12d25a
　　　　　　C9am12d25a

【参考資料】
【1日1回】本能が告げている 危険だと＞1日1回
[https://docs.google.com/spreadsheets/d/1jDHRT1GvOA-K4WUhru1CV7Ukn63owsYJXFbbr6IB8eo/edit?gid=856977995#gid=856977995](https://docs.google.com/spreadsheets/d/1jDHRT1GvOA-K4WUhru1CV7Ukn63owsYJXFbbr6IB8eo/edit?gid=856977995#gid=856977995)

【報告者】
福元 浩晃

![](https://t5716001.p.clickup-attachments.com/t5716001/47aeeb73-9ea3-4c79-ba27-5b5f8eb9531a/%E5%9C%B0%E7%8D%84%E6%A5%BD_1%E6%97%A51%E5%9B%9E_%E3%82%AF%E3%82%A8%E3%82%B9%E3%83%88%E8%A8%AD%E5%AE%9A.jpg)

