# 「B小町不動のセンター アイ」にて、通常攻撃の吹き出しアセットキーが資料と相違している

【発生症状】
「B小町不動のセンター アイ」において、
通常攻撃の吹き出しアセットキーが資料とマスターで相違しています。

　▼吹き出しアセット
　・資料　　：Bishi、Doka2、Ga（shageki\_1）
　・マスター：Do、Doka、Doka2（dageki\_1）

【再現手順】
1.資料「ヒーロー基礎設計\_chara\_osh\_00001\_B小町不動のセンター アイ」を開く。
2.通常攻撃ステータスの「オノマトペ」を確認する。
3.「shageki\_1」と記載されていることを確認する。
4.デバッグメニュー>ユニットマスターデータチェック>B小町不動のセンター アイを選択する。
5.攻撃ステータスタブ内の通常攻撃基本情報で「吹き出しアセットキー」を確認する。
6.「Do、Doka、Doka2」（dageki\_1）と表示されていることを確認する。

【補足】
なし

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa2
検証ビルド：iOS #2533
　　　　　　aOS #2533
検証端末：iPhoneSE3(iOS:18.6)
　　　　　ZenFone6(AOS:9.0)

【ユーザー情報】
ユーザーID：A7176478340
　　　　　　A8892437674
ユーザー名：海豚
　　　　　　河豚

【参考資料】
[https://docs.google.com/spreadsheets/d/1454SOfo66TYkAa49Ims9YFu1GkF0vy9Nlfa8vkWbSv8/edit?gid=523149893#gid=523149893](https://docs.google.com/spreadsheets/d/1454SOfo66TYkAa49Ims9YFu1GkF0vy9Nlfa8vkWbSv8/edit?gid=523149893#gid=523149893)

【報告者】
安達 和也

![](https://t5716001.p.clickup-attachments.com/t5716001/4facce00-7c49-4687-b9f6-e57361cb37a3/%E3%82%A2%E3%82%A4_%E9%80%9A%E5%B8%B8%E6%94%BB%E6%92%83%E3%81%AE%E5%90%B9%E3%81%8D%E5%87%BA%E3%81%97.jpg)

