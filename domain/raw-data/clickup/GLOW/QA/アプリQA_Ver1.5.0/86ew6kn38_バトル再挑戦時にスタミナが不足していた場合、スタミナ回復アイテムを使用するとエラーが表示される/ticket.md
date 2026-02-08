# バトル再挑戦時にスタミナが不足していた場合、スタミナ回復アイテムを使用するとエラーが表示される

【発生症状】
バトル再挑戦時にスタミナが不足していた場合、
スタミナ不足ダイアログからスタミナ回復アイテムの回復ボタンをタップ時にエラーが表示されております。

【再現手順】
1.スタミナドリンクかスタミナボトルを1個以上所持する。
2.任意のクエストを一度クリアする。
3.バトルが1回のみ可能なスタミナ値がある状態で再度クエストをクリアする。
4.リザルト画面で再挑戦ボタンをタップする。
5.スタミナ不足ダイアログが表示時、スタミナドリンクかスタミナボトルの回復ボタンをタップする。
6.エラーが表示されることを確認する。

【補足】
・エラーログ内容
【コード：CLE-1】
Zenject.ZenjectExeption

【再現性】
3回中3回

【環境情報】
接続環境：develop
検証ビルド：iOS #5(541)
　　　　　　AOS #5(544)
検証端末：iPhone13(iOS:17.0.3)
　　　　　Pixel9(AOS:14.0.0)

【ユーザー情報】
ユーザーID：A2219519496
　　　　　　A4219692519
ユーザー名：C9im01d13a
　　　　　　C9am01d13a

【参考資料】
スタミナ回復アイテム
[https://docs.google.com/presentation/d/1tBpInDJcKFMsXyQ0u7Ig4uI9l1FG4fBB7n\_5DoWiRHg/edit?slide=id.g3b05864a0bd\_0\_17#slide=id.g3b05864a0bd\_0\_17](https://docs.google.com/presentation/d/1tBpInDJcKFMsXyQ0u7Ig4uI9l1FG4fBB7n_5DoWiRHg/edit?slide=id.g3b05864a0bd_0_17#slide=id.g3b05864a0bd_0_17)

【報告者】
福元 浩晃

![](https://t5716001.p.clickup-attachments.com/t5716001/4c669a1d-5ab6-4974-9fb2-8d2c7fd840a5/%E3%82%A8%E3%83%A9%E3%83%BC%E8%A1%A8%E7%A4%BA_%E5%86%8D%E6%8C%91%E6%88%A6%E6%99%82%E3%82%B9%E3%82%BF%E3%83%9F%E3%83%8A%E5%9B%9E%E5%BE%A9%E3%82%A2%E3%82%A4%E3%83%86%E3%83%A0%E4%BD%BF%E7%94%A8.jpg)

