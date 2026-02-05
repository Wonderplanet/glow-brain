# Slack Thread Export

**Channel:** glow_wp (`C069N3UL80H`)
**Thread TS:** 1768812361.866389
**Exported at:** 2026-01-23 22:02:58

---

## Parent Message

**Author:** Toshikazu Takahashi (`U01DXPA51L1`)
**Timestamp:** 2026-01-19 17:46:01 (`1768812361.866389`)

**Message:**

```
<@UD57AC0EB> <@U01SLEEPKQD> <@U02CFE058EN> <@U01TLE2AX8Q> ( <@U9YULJPUZ> <@U0113RN4C5S> <@U2WK72DBN>
アナウンス漏れてました。
QA環境をv1.5.0への更新をお願いします。
<https://docs.google.com/spreadsheets/d/1FJeOKFEltBWGpd-7jdZUoVZKtqBtfhwYpGOVb8Dt1nQ/edit?gid=0#gid=0&amp;range=ET46:EX46>
```


## Replies

### Reply 1

**Author:** Shun Endo (`UD57AC0EB`)
**Timestamp:** 2026-01-19 17:55:16 (`1768812916.208279`)

**Message:**

```
デプロイ開始:genbaneko06:
```


### Reply 2

**Author:** Junki Furusawa (`U01TLE2AX8Q`)
**Timestamp:** 2026-01-20 12:47:37 (`1768880857.650749`)

**Message:**

```
<!subteam^S07RJ1RBVUY>
QA環境でマスタ取り込み時、全てにチェック入れてインポートしようとすると414エラー画面になってしまうのですが、解消可能でしょうか？

全チェックしなければエラーにならないので、ひとまず昇格作業はチェック分割して進めます。
```

**Attachments:**

- `スクリーンショット 2026-01-20 12.43.25.png` (image/png, 44047 bytes)
  - Path: [attachments/F0A9Q8EF1TL_スクリーンショット_2026-01-20_12.43.25.png](attachments/F0A9Q8EF1TL_スクリーンショット_2026-01-20_12.43.25.png)


### Reply 3

**Author:** Shun Endo (`UD57AC0EB`)
**Timestamp:** 2026-01-20 14:08:06 (`1768885686.961069`)

**Message:**

```
あーこれ全チェックすると全部がGETパラメーターに乗るやつ？ｗ
```


### Reply 4

**Author:** Shun Endo (`UD57AC0EB`)
**Timestamp:** 2026-01-20 14:14:44 (`1768886084.204639`)

**Message:**

```
ydsのときは全チェックするとパラメーターがisAllみたいなのに変わるようにしてたからそういう方向性かなぁ
（いつも全チェックの時だけパラメーター変えてたからこのエラー初めて見た:face_with_rolling_eyes:）
```


### Reply 5

**Author:** Junki Furusawa (`U01TLE2AX8Q`)
**Timestamp:** 2026-01-20 15:52:16 (`1768891936.377629`)

**Message:**

```
<!subteam^S07RJ1RBVUY>
QA環境ですが、MstAttackElementにチェックを入れるとエラーになってしまうので、ご確認お願いします。

構造差分の変更がたくさん出る影響でタイムアウトになってるっぽい…？
```

**Attachments:**

- `スクリーンショット 2026-01-20 15.41.31.png` (image/png, 1149436 bytes)
  - Path: [attachments/F0A99HCGB2B_スクリーンショット_2026-01-20_15.41.31.png](attachments/F0A99HCGB2B_スクリーンショット_2026-01-20_15.41.31.png)


### Reply 6

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-20 16:05:46 (`1768892746.037559`)

**Message:**

```
タイムアウト延長設定がもう一箇所必要かも
```


### Reply 7

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-20 16:07:44 (`1768892864.273639`)

**Message:**

```
qa環境のコードを調整してみます
```


### Reply 8

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-20 16:15:20 (`1768893320.323079`)

**Message:**

```
<@U01TLE2AX8Q> 少し調整してみました、再度MstAttackElementの投入してみていただいてよろしいでしょうか:man-bowing::skin-tone-2:
```


### Reply 9

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-20 16:18:39 (`1768893519.692359`)

**Message:**

```
サーバーメモ：
importメソッドではmax_execution_timeを伸ばしてたが
スプシからデータ取得するmountメソッドの方では、伸ばしてなかった

なのでmountでも伸ばすように追加してみた

挙動問題なければ、PR出してマージする
```


### Reply 10

**Author:** Junki Furusawa (`U01TLE2AX8Q`)
**Timestamp:** 2026-01-20 16:29:35 (`1768894175.567819`)

**Message:**

```
<@U01SLEEPKQD>
インポートできました！
ご対応ありがとうございました。
```


### Reply 11

**Author:** Junki Furusawa (`U01TLE2AX8Q`)
**Timestamp:** 2026-01-20 17:01:38 (`1768896098.802859`)

**Message:**

```
<@U2WK72DBN> <@U01DXPA51L1> (<@U02CFE058EN> <@U9YULJPUZ> <@UD57AC0EB>
QA環境へ、1.5.0/202602010の昇格完了しました。
```

