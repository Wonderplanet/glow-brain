# 【仕様確認】氷結や弱体化が付与された状態のJUMBLE RUSHのトータルダメージ表示について

【発生症状】
資料「バトル演出追加とインゲームキャラ詳細改善」において、
氷結や弱体化でダメージが増す場合、トータルダメージに反映する旨が記載されていますが、
Confluence「51-1-13-4\_総攻撃ダメージ計算」では氷結等は『計算に含まない』との記載がございます。

現状、実動作はConfluenceに記載の内容のとおりに氷結等はダメージ計算に含まれておりませんが、
v1.4.1ではどちら正しい動作となりますでしょうか。

【再現手順】
1.任意のクエストに挑戦する。
2.JUMBLE RUSHを使用し、トータルダメージを記録する。
3.敵に氷結や弱体化を付与した後、再度JUMBLE RUSHを使用する。
4.トータルダメージが手順2.と変わらないことを確認する。

【補足】
なし

【再現性】
3回中3回

【環境情報】
接続環境：dev-qa
検証ビルド：iOS #44(533)
　　　　　　aOS #43(536)
検証端末：iPhoneSE3(iOS:16.2)
　　　　　Galaxy S9+ SCV39(AOS:8.0)

【ユーザー情報】
ユーザーID：A5111107160
　　　　　　A2095259635
ユーザー名：色
　　　　　　某

【参考資料】
バトル演出追加とインゲームキャラ詳細改善>P.4
[https://docs.google.com/presentation/d/1yg1W1K23IwZbppYNpIJBcgsJkEOFDFTVIn7wuWxrqKw/edit?slide=id.g3ad574b8d07\_1\_185#slide=id.g3ad574b8d07\_1\_185](https://docs.google.com/presentation/d/1yg1W1K23IwZbppYNpIJBcgsJkEOFDFTVIn7wuWxrqKw/edit?slide=id.g3ad574b8d07_1_185#slide=id.g3ad574b8d07_1_185)

Confluence「51-1-13-4\_総攻撃ダメージ計算」
[https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/457867265/51-1-13\_#51-1-13-4\_%E7%B7%8F%E6%94%BB%E6%92%83%E3%83%80%E3%83%A1%E3%83%BC%E3%82%B8%E8%A8%88%E7%AE%97](https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/457867265/51-1-13_#51-1-13-4_%E7%B7%8F%E6%94%BB%E6%92%83%E3%83%80%E3%83%A1%E3%83%BC%E3%82%B8%E8%A8%88%E7%AE%97)

【報告者】
安達 和也

