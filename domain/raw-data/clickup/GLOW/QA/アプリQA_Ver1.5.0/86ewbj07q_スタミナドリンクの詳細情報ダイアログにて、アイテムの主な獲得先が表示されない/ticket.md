# スタミナドリンクの詳細情報ダイアログにて、アイテムの主な獲得先が表示されない

【発生症状】
スタミナドリンクの詳細情報ダイアログにて、アイテムの主な獲得先が表示されておりません。

▼仕様書
・その他
スタミナドリンクのアイコンをタップして表示されるアイテム詳細ダイアログには獲得先として「ショップ」を必ず表示するように設定する。

▼実機
表示されていない。

※現時点では常設のショップでは無くパック販売とログインボーナスでの獲得となるため、獲得できない期間があるかと思われます。

【再現手順】
1.スタミナドリンクを付与する。
2.アイテムBOX、スタミナ回復時のダイアログ等で、アイテムアイコンをタップする。
3.詳細情報に主な獲得先が表示されていないことを確認する。

【補足】
Develop環境で確認した際は表示されていることを確認しております。
・関連報告
スタミナ回復アイテムの詳細情報ダイアログにて、アイテムの獲得先が表示されない
スタミナ回復アイテムの詳細情報ダイアログにて、アイテムの獲得先が表示されない ([https://app.clickup.com/t/86ew6wj4x](https://app.clickup.com/t/86ew6wj4x))

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa2/qa
検証ビルド：iOS #37(580)
　　　　　　AOS #37(581)
検証端末：iPhone13(iOS:17.0.3)
　　　　　Pixel9(AOS:14.0.0)

【ユーザー情報】
ユーザーID：A4107286240
　　　　　　A2222682949
ユーザー名：C9im01d26a
　　　　　　C9am01d26a

【参考資料】
スタミナ回復アイテム
[https://docs.google.com/presentation/d/1tBpInDJcKFMsXyQ0u7Ig4uI9l1FG4fBB7n\_5DoWiRHg/edit?slide=id.g3b05864a0bd\_0\_17#slide=id.g3b05864a0bd\_0\_17](https://docs.google.com/presentation/d/1tBpInDJcKFMsXyQ0u7Ig4uI9l1FG4fBB7n_5DoWiRHg/edit?slide=id.g3b05864a0bd_0_17#slide=id.g3b05864a0bd_0_17)
GLOW\_ID 管理＞アイテム
[https://docs.google.com/spreadsheets/d/1oJmkDK37Qizzdaxn4KCwh1BMwdNWuHCx3AGH8BC9yu0/edit?gid=1994449604#gid=1994449604](https://docs.google.com/spreadsheets/d/1oJmkDK37Qizzdaxn4KCwh1BMwdNWuHCx3AGH8BC9yu0/edit?gid=1994449604#gid=1994449604)

【報告者】
福元 浩晃

![](https://t5716001.p.clickup-attachments.com/t5716001/544ee5a7-ad1c-4c67-9254-2645718a5e1e/%E3%82%B9%E3%82%BF%E3%83%9F%E3%83%8A%E3%83%89%E3%83%AA%E3%83%B3%E3%82%AF_%E8%A9%B3%E7%B4%B0%E6%83%85%E5%A0%B1.jpg)

