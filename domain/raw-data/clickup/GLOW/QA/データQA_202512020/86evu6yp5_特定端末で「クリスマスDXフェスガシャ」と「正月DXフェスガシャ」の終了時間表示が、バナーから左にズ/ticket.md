# 特定端末で「クリスマスDXフェスガシャ」と「正月DXフェスガシャ」の終了時間表示が、バナーから左にズレて表示されている

【発生症状】
特定端末において、「クリスマスDXフェスガシャ」と「正月DXフェスガシャ」のバナー左上に表示されている、
「残り時間表示」が画面左側へズレて表示されております。

▼該当端末
・iPad mini（第6世代）
・Galaxy Tab S7

【再現手順】
1.管理ツールで日時を「2025/12/22 12:00:00」に設定する
2.ホーム画面＞ガシャをタップし、ガシャ一覧画面へ遷移する
3.フェスガシャの「クリスマスDXフェスガシャ」バナー左上、「残り時間表示」が画面左側へズレていることを確認する
4.管理ツールで日時を「2026/1/1 00:00:00」に設定する
2.ホーム画面＞ガシャをタップ、ガシャ一覧画面へ遷移する
3.フェスガシャの「正月DXフェスガシャ」バナー左上、「残り時間表示」が画面左側へズレていることを確認する

【補足】
なし

【再現性】
3回中3回

【環境情報】
接続環境　：dev-qa2
検証ビルド：iOS #2528
　　　　　：AOS #2528
検証端末　：iPad mini（第6世代）（iOS:18.2）
　　　　　：Galaxy Tab S7（AOS:10.0）

【ユーザー情報】
ユーザーID：A8699084069
　　　　　：A3120041414
ユーザー名：双ノ子
　　　　　：ふたりのざ

【参考資料】
「20251222\_クリスマスフェス限\_イベント＋ガシャ＋パック仕様書」>「06\_クリスマスDXフェスガシャ\_設計書」
[https://docs.google.com/spreadsheets/d/1-KZyI41POmGYGsWqJCtrjgorP5Pk4-IilCtgZIjaUSk/edit?gid=1315731856#gid=1315731856](https://docs.google.com/spreadsheets/d/1-KZyI41POmGYGsWqJCtrjgorP5Pk4-IilCtgZIjaUSk/edit?gid=1315731856#gid=1315731856)

「正月特別号！【推しの子】 いいジャン祭＋メインクエスト\_仕様書」>「06\_正月DXフェスガシャ\_設計書」
[https://docs.google.com/spreadsheets/d/1d2uaObkbzpp7a6FJdEBh8LqP5wmen0u57HEEiq8E-bM/edit?gid=1315731856#gid=1315731856](https://docs.google.com/spreadsheets/d/1d2uaObkbzpp7a6FJdEBh8LqP5wmen0u57HEEiq8E-bM/edit?gid=1315731856#gid=1315731856)

【報告者】
堤 崇

![](https://t5716001.p.clickup-attachments.com/t5716001/32671589-b3f6-4400-819c-db56e3ab2e01/%E7%89%B9%E5%AE%9A%E7%AB%AF%E6%9C%AB_%E3%83%95%E3%82%A7%E3%82%B9%E3%82%AC%E3%82%B7%E3%83%A3%E3%83%90%E3%83%8A%E3%83%BC%E3%81%AE%E6%AE%8B%E3%82%8A%E6%99%82%E9%96%93%E3%81%8C%E7%94%BB%E9%9D%A2%E5%B7%A6%E3%81%AB%E3%82%BA%E3%83%AC%E3%81%A6%E3%81%84%E3%82%8B.jpg)

