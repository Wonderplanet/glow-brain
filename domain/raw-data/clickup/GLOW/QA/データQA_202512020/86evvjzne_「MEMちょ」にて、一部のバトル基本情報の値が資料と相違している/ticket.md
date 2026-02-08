# 「MEMちょ」にて、一部のバトル基本情報の値が資料と相違している

【発生症状】
「MEMちょ」において、「召喚クールタイム/最小攻撃力/最大攻撃力」の値が
資料とマスターで相違しています。

　▼該当情報の値
　召喚クールタイム
　・資料　　：325
　・マスター：270

　最小攻撃力
　・資料　　：480
　・マスター：630

　最大攻撃力
　・資料　　：4800
　・マスター：6300　

【再現手順】
1.資料「ヒーロー基礎設計\_chara\_osh\_00301\_MEMちょ」を開く。
2.基本ステータスで「再召喚時間F」、Lvステータスで「攻撃力Lv1」「攻撃力Lv150」を確認する。
3.デバッグメニュー>ユニットマスターデータチェック>MEMちょを選択する。
4.攻撃ステータスタブのバトル基本情報で、「召喚クールタイム」「最小攻撃力」「最大攻撃力」を確認する。

【補足】
なし

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa2
検証ビルド：iOS #2546
　　　　　　aOS #2547
検証端末：iPhoneSE3(iOS:18.6)
　　　　　ZenFone6(AOS:9.0)

【ユーザー情報】
ユーザーID：A1630045287
　　　　　　A1923806072
ユーザー名：ビタミンB1
　　　　　　ビタミンB2

【参考資料】
[https://docs.google.com/spreadsheets/d/1P7pLvuUd0-yl\_JNHnqGCWCTIvFB08KPitEnUJzpl6sU/edit?gid=523149893#gid=523149893](https://docs.google.com/spreadsheets/d/1P7pLvuUd0-yl_JNHnqGCWCTIvFB08KPitEnUJzpl6sU/edit?gid=523149893#gid=523149893)

【報告者】
安達 和也

![](https://t5716001.p.clickup-attachments.com/t5716001/6577e3f1-2e2d-4c0e-9e2c-7b442e2a086c/MEM%E3%81%A1%E3%82%87_%E3%83%90%E3%83%88%E3%83%AB%E5%9F%BA%E6%9C%AC%E6%83%85%E5%A0%B1.jpg)

