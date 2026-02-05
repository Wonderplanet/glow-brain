# アイテムBOXにて、タブ「消費」にあるアイテムをタップ時にエラーが表示される

【発生症状】
アイテムBOXにて、タブ「消費」にある任意のアイテムをタップ時にエラーが表示されております。

※ランクマッチチケットのみエラーが表示されず、その他のアイテム(ランダムRキャラのかけらBOX等)でエラーが表示されます。

▼エラー内容冒頭
【コード：CLE-1】
System.Reflection.TargetInvocationException:Exception has been thrown by the target of an invocation.

【再現手順】
1.任意のプレイヤーデータを作成する。
2.デバッグメニュー＞リモート＞ユーザーの所持アイテム付与＆MAXを使用する。
3.ホーム画面＞アイテムBOXをタップする。
4.タブ「消費」を表示し、任意のアイテムアイコンをタップする。

【補足】
下記の各環境でエラーが発生することを確認。
develop、dev-ld、dev-qa2

【再現性】
3回中3回

【環境情報】
接続環境：develop、dev-ld、dev-qa2
検証ビルド：iOS #2490
　　　　　　AOS #2490
検証端末：iPhone13(iOS:17.0.3)
　　　　　Pixel9(AOS:14.0.0)

【ユーザー情報】
ユーザーID：A2307311199
　　　　　　A2689484650
ユーザー名：CI09i02d1
　　　　　　CI09a02d1

【参考資料】
改善要件：交換所の実装- v.1.4.0
[https://docs.google.com/presentation/d/10vFEQKrOIrJNeSVQJ7SGUqhrgoVl5Nd915PWWDepupg/edit?slide=id.g39e76f1982f\_0\_326#slide=id.g39e76f1982f\_0\_326](https://docs.google.com/presentation/d/10vFEQKrOIrJNeSVQJ7SGUqhrgoVl5Nd915PWWDepupg/edit?slide=id.g39e76f1982f_0_326#slide=id.g39e76f1982f_0_326)

【報告者】
福元 浩晃

![](https://t5716001.p.clickup-attachments.com/t5716001/1e91e598-ba71-4cf6-bbe3-1d2a80a3547c/%E6%B6%88%E8%B2%BB%E3%82%BF%E3%83%96_%E3%82%A8%E3%83%A9%E3%83%BC.jpg)

