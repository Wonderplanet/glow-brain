# 「星野 ルビー」の必殺ワザにて、グレード2以上の効果値が資料と相違している

【発生症状】
「星野 ルビー」の必殺ワザにおいて、グレード2以上の効果値が資料とマスターで相違しています。

なおマスターが正しい場合、必殺ワザ文言は資料の値で表記されているため、
必殺ワザ文言の値も修正頂けますようお願いいたします。

　▼各グレードの効果値（攻撃アップ）
　グレード2
　・資料　　：17.1
　・マスター：17

　グレード3
　・資料　　：18.5
　・マスター：19

　グレード4
　・資料　　：19.4
　・マスター：22

　グレード5
　・資料　　：20
　・マスター：25

【再現手順】
1.資料「ヒーロー基礎設計\_chara\_osh\_00201\_星野 ルビー」を開く。
2.必殺ワザ詳細の必殺ワザ1で、各グレードの効果値を確認する。
3.デバッグメニュー>ユニットマスターデータチェック>星野ルビーを選択する。
4.攻撃ステータスタブの必殺ワザ当たり判定情報で攻撃アップの付与効果効果値を確認する。

【補足】
なし

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa2
検証ビルド：iOS #2544
　　　　　　aOS #2544
検証端末：iPhoneSE3(iOS:18.6)
　　　　　ZenFone6(AOS:9.0)

【ユーザー情報】
ユーザーID：A9789710268
　　　　　　A2537988108
ユーザー名：漢字
　　　　　　完治

【参考資料】
[https://docs.google.com/spreadsheets/d/1kqwhHAX8DRZSJmEgYJC5raksiwDHaxWqXL\_t5E5Rt4Y/edit?gid=523149893#gid=523149893](https://docs.google.com/spreadsheets/d/1kqwhHAX8DRZSJmEgYJC5raksiwDHaxWqXL_t5E5Rt4Y/edit?gid=523149893#gid=523149893)

【報告者】
安達 和也

![](https://t5716001.p.clickup-attachments.com/t5716001/834641d7-af41-49e1-99dc-cadfb6067206/%E3%83%AB%E3%83%93%E3%83%BC_%E5%BF%85%E6%AE%BA%E3%83%AF%E3%82%B6(%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%892).jpg)
![](https://t5716001.p.clickup-attachments.com/t5716001/aceeff62-7b00-478e-a28b-55f2e50cdb6d/%E3%83%AB%E3%83%93%E3%83%BC_%E5%BF%85%E6%AE%BA%E3%83%AF%E3%82%B6(%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%893).jpg)
![](https://t5716001.p.clickup-attachments.com/t5716001/b423219d-0073-47d1-a51e-22bf2fd5400c/%E3%83%AB%E3%83%93%E3%83%BC_%E5%BF%85%E6%AE%BA%E3%83%AF%E3%82%B6(%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%894).jpg)
![](https://t5716001.p.clickup-attachments.com/t5716001/de5b7438-2b55-4ff8-ae04-8d3b15055de2/%E3%83%AB%E3%83%93%E3%83%BC_%E5%BF%85%E6%AE%BA%E3%83%AF%E3%82%B6(%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%895).jpg)

