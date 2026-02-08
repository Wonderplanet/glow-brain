# 「MEMちょ」の必殺ワザにて、攻撃範囲等のコマ位置が資料と相違している

【発生症状】
「MEMちょ」の必殺ワザにおいて、「射程/攻撃範囲/range\_end\_parameter」のコマ位置が
資料では『-1 (一つ手前)』、マスターでは「1 (一つ先)」となっており相違しています。

必殺ワザ文言はマスターの設定を元に『一つ先』と表記されているため、
資料の『一つ手前』が正しい場合は、必殺ワザ文言も合わせて修正をお願いいたします。

【再現手順】
1.資料「ヒーロー基礎設計\_chara\_osh\_00301\_MEMちょ」を開く。
2.必殺ワザステータスの「射程」「攻撃範囲」「range\_end\_parameter」を確認する。
3.デバッグメニュー>ユニットマスターデータチェック>MEMちょを選択する。
4.攻撃ステータスタブの必殺ワザ当たり判定情報で、「range\_end\_parameter」を確認する。

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

![](https://t5716001.p.clickup-attachments.com/t5716001/5a591638-4aed-4584-8307-d1f1a8944e5a/MEM%E3%81%A1%E3%82%87_%E5%BF%85%E6%AE%BA%E3%83%AF%E3%82%B6%E3%81%AE%E3%82%B3%E3%83%9E%E4%BD%8D%E7%BD%AE.jpg)

