# Slack Thread Export

**Channel:** glow_wp (`C069N3UL80H`)
**Thread TS:** 1768381036.673559
**Exported at:** 2026-01-23 22:06:17

---

## Parent Message

**Author:** Toshikazu Takahashi (`U01DXPA51L1`)
**Timestamp:** 2026-01-14 17:57:16 (`1768381036.673559`)

**Message:**

```
<!subteam^S07QV5H2LLS>
お疲れ様です。

各自で行っている業務効率をあげている施策・試み・ツールなどを共有してほしいです。
例としては、
・お知らせのトンマナチェックをAIを使っている
・スケジュールのリマインドツールをAI使って作成した
・リリースタイムラインの作成をSlackワークフローで自動化した（<https://wonderplanet-glow.slack.com/archives/C08KFEGU7PE/p1768274812025599?thread_ts=1768272362.592959&amp;cid=C08KFEGU7PE>）
など、　小さいものから大きいものまで、なんでもいいので教えてください。
明日（1/15）いっぱいで、スレッドに投稿してもらえると助かりますー
```


## Replies

### Reply 1

**Author:** Toshikazu Takahashi (`U01DXPA51L1`)
**Timestamp:** 2026-01-14 18:00:20 (`1768381220.391859`)

**Message:**

```
自分が作ったものも記載しておく

• <https://wonderplanet-glow.slack.com/archives/C08KEGAV23D/p1766734597425469?thread_ts=1766734590.853159&cid=C08KEGAV23D|障害報告書自動作成>
• <https://wonderplanet-glow.slack.com/archives/C08KEGAV23D/p1766734598750969?thread_ts=1766734590.853159&cid=C08KEGAV23D|障害報告タスク自動作成>
• <https://wonderplanet-glow.slack.com/archives/C09V7638J03/p1764292230823899|BNE依頼をAIに判断させてタスク化するワークフロー（n8n）>
• <https://wonderplanet-glow.slack.com/archives/C064RM1QX8A/p1763640899655649|バグチケが起票された時に、「調査方針・解決策・対応者の提案」をコメントに記載するn8n>
• <https://wonderplanet-glow.slack.com/archives/C08KFEGU7PE/p1768274812025599?thread_ts=1768272362.592959&cid=C08KFEGU7PE|リリースタイムライン作成ワークフロー>
• <https://wonderplanet-glow.slack.com/archives/C064RM1QX8A/p1766640626258189|ウィンセッション自動通知>
• <https://wonderplanet-glow.slack.com/archives/C064RM1QX8A/p1766737599292529|リアクションランキング>
• <https://wonderplanet-glow.slack.com/archives/C069N3UL80H/p1768381220391859?thread_ts=1768381036.673559&cid=C069N3UL80H|Gemを使った誤字脱字チェッカー>
```


### Reply 2

**Author:** Rikaco Kobayashi (`UMV9ZG9NU`)
**Timestamp:** 2026-01-14 18:05:57 (`1768381557.392059`)

**Message:**

```
・他社ゲーのお知らせをAIに読み込ませてGLOW用に書き換えさせた
・企画に使うクエスト名、エンブレム名をAIに候補を挙げてもらった
・監修企画のスケジュールを自動で出させるための関数をAIに組ませた
・お知らせの昇格日を自動的に出させるための関数をAIに組ませた
・探してる原作のコマが何巻なのかをAIに聞いたらめっちゃ時短になった（５割当たる）
（こんなかんじであってますか…？）
```


### Reply 3

**Author:** Kenta Inui (`U1RJ7FH6J`)
**Timestamp:** 2026-01-14 18:14:12 (`1768382052.163359`)

**Message:**

```
自分こんな感じです:ojigi:

*メモとしてのvscode使用*
• 日報などをテンプレから複製
• 箇条書きした内容のまとめ
• ファイル検索、追加
    ◦ 〜ってどこにメモった？
    ◦ 〜について記載する場所がほしい

*機能実装*
ざっくり下記の流れで使っています
1. Geminiで要件スライドのmd形式要約
2. Copilotと機能実装計画擦り合わせ
3. Agentで機能実装
4. Copilotとユニットテスト実装計画擦り合わせ
5. Agentでユニットテスト実装

```


### Reply 4

**Author:** Donghyeok Kim (`UBRD602PN`)
**Timestamp:** 2026-01-14 18:21:13 (`1768382473.694069`)

**Message:**

```
<@U01DXPA51L1> cc <@UUMG9SH37>
• ドン作成
<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/923402248|【ツール】キャラアセット実装ツール>
　→キャラの画像アセットの実装が簡単にできるツール

<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/922779668|【ツール】キャラ/エネミーアセット確認ツール>
　→キャラの画像アセットの実装に問題ないか簡単に確認できるツール

<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1219821569|【ツール】キャラモーションデータチェックツール（改修中）>
　→モーションアセットが仕様通りに作られているか確認できるツール

<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1172897793/PSD|【ツール】漫画PSDコマ切り抜きツール >
　→クエスト用コマ素材を切り抜くツール

<https://docs.google.com/presentation/d/1CRKA_3YxdmHgsl5XEgh8ICWmkqkqNoU0rLQFy_T1bkQ/edit?slide=id.g47c21c12e6_0_417#slide=id.g47c21c12e6_0_417|【ナレッジ】AIと制作を分担する働き方>
　→デザイナー向けのAI活用ナレッジ資料

• 大川さんメインで作成↓
<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1212579842|【ツール】スマートオブジェクトのリンク確認ツール>
　→PSD内のスマートオブジェクトが問題ない構造になっているか確認できるツール
```


### Reply 5

**Author:** Hiroki Fujita (`U2WK72DBN`)
**Timestamp:** 2026-01-14 18:37:36 (`1768383456.381819`)

**Message:**

```
基本的にAIツールでGASや関数を作成する形で使用
```・TCの叩きを作成(notebookLM)
・QAスケジュールで使用するGASの作成(Gemini)
・BPの稼働管理表の関数組み直し(Copilot)
・リリースキー別の不具合集計作成(notebookLM)
・QA共有シートのGAS作成(Gemini)```
```


### Reply 6

**Author:** Issei Takahashi (`U07HEBPUW4E`)
**Timestamp:** 2026-01-14 19:26:37 (`1768386397.863119`)

**Message:**

```
• 髙橋作成
キャラ設計の品質維持・工数削減のために、以下のような取り組み行っています。
(ツールとは違うかもですが:man-bowing:)

【業務自動化・効率化の取り組み（キャラクター設計における設計品質のブレを抑える運用）】

• キャラクター設計における設計資料の手動入力と設定ミスを想定し、
レギュレーション参照および数値入力を自動化する仕組みを構築・運用している
※1キャラあたり約110〜130項目の設計が必要&資料に手動入力していた設計作業を、
プルダウン選択による誤入力防止&自動算出に置き換えている（最低限必要な設定項目を15〜20項目程度まで削減※キャラに依る）
　例：各威力効果値、範囲・レアリティ・グレード等の数値レギュレーションを参照し、
　　　設計意図に沿った数値が自動で反映される構成としている
• 設計資料からそのまま数値としてマスター入力を行える構成とし、
　資料と実データの相違が起きにくい構成で資料を構築
• キャラクター承認用資料と設計資料を同一データ参照とし、
　承認・QA時など設計時の内容と記載相違が起きにくい状態を構築
• 設計時の各設定項目について、全体バランスを確認できる簡易集計ツールを作成
(個人作業効率化：資料からマスタに反映が必要な項目、且つ、
元資料形式的に記載項目をそのままマスタ反映できない箇所は、
可能な限りGASを使って効率化処理&データ反映ミス防止)
※新ワザ・新特性追加時・環境調整時など現在も随時、更新中
```


### Reply 7

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-15 10:41:29 (`1768441289.858359`)

**Message:**

```
• サーバー
    ◦ AIでPR作成するコマンドを作った
        ▪︎ レビュワーにとって、なぜその対応がなぜ必要だったかの背景情報や、どんな変更をしたのかのサマリがあると、レビューをスムーズに進めやすい
        ▪︎ その結果、レビューが早く高品質に進められるので、実装者にとっても、スケジュール的にも良い
    ◦ AIでAPIのテスト実行と自動修正するコマンドを作った
        ▪︎ 手動で1つずつテスト確認して修正するのは手間がかかる。その上そんなに手間をかけるべきものでもないことが多い
        ▪︎ 上記を全部自動化して、開発効率アップ
    ◦ 仕様書から実装要件整理とAPI設計書の生成
        ▪︎ 仕様書に記載はないが、サーバー実装的に必要な要件などを、設計段階で検知して、良質な設計やスケジュール遅延可能性の低減効果
    ◦ 自動レビュー項目の拡充(CodeRabbitAI)
        ▪︎ 変数名名などの小さい内容から、仕様との乖離部分のチェックまで、実装・レビュー効率をアップするAIの設定拡充をした
    ◦ 管理ツールの実装を、AIが自動でブラウザを使って画面を見ながら動作確認できるようにした
        ▪︎ 管理ツールではAPIのようにテストを作る意味が弱いが、画面の確認はしたい。だがこれは人が見るしかなかった。これをAIができるようにした
    ◦ 障害再発防止のための実装確認シートの自動追加と記入催促をするgithub action workflowをAIで作った
    ◦ 実装方法の手引きドキュメント(Agent skills)の量産
        ▪︎ 多種多様な実装タスクを、既存実装などを参考にスピードも質も高く、実装完了できるようにするため
• その他
    ◦ 試み：マスタデータ設定方法を相談するAI窓口
        ▪︎ 例えば、boxガシャどうやって設定すればいい？と聞いたら、このテーブルでこんな感じの設定をしてくださいと回答できるようにした
    ◦ 試み：マスタデータを設計書から自動生成するAIツール
    ◦ 試み：GLOWのことなら何でも答えられるよRepositoryの準備中（glow-brain）
        ▪︎ 実装コード、マスタデータ、仕様書、QA,TC、KPI、デザインなどなど、GLOWに関するあらゆる情報を1箇所にまとめて、全ての情報をAIで使えるようにすれば、全チームの作業効率up、アイデアだしとかのための基盤にできるかもしれないという理想
```


### Reply 8

**Author:** Aya Masakiyo (`U87JG8W8Z`)
**Timestamp:** 2026-01-15 11:20:13 (`1768443613.109339`)

**Message:**

```
ブラッシュアップ中のため配布はまだですが、PhotoshopのプラグインをGeminiで作って時短化成功しました。

*当て込み配置プラグイン*
• 確認する際に必要なキャライラストの当て込みを作る際に、ワンポチで選択した複数キャラを縮小し画面上に配置できるようにしました。今まで１体ずつアクションをかけ手動配置していたのでワンポチでできるようになりかなり早くなりました。
```


### Reply 9

**Author:** Keisuke Isayama (`U08KFA64REY`)
**Timestamp:** 2026-01-15 11:32:00 (`1768444320.630979`)

**Message:**

```
テストをAgentに書いてもらうためのプロンプト生成ツールをGeminiに作ってもらいました。
→Agent(Cloaude Sonnet 4.5)にテスト作成時のプロンプトのフォーマットを作成してもらい、Geminiに簡単にチェックボックスなどでどんなテストを用意してほしいかなどを選択した結果をもとにフォーマットに沿ってプロンプトを作成してもらう感じです。ただ、無駄にメソッドを分けたりと精度はまだいまいちなので、ブラッシュアップしたいです。
```


### Reply 10

**Author:** Kiryu Yamashita (`UHHSKP17Z`)
**Timestamp:** 2026-01-15 11:50:44 (`1768445444.967529`)

**Message:**

```
(主にGithub Copilotにはなりますが...)
```コードリーディングをする際、github copilotのAgentモードを使用してmdファイルにまとめてもらう
-> 不具合調査時、設計時などで使用(下記で資料化)
<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1393295386/Copilot>

コードの改行調整、簡単なテストの作成・修正(Copilot)
-> 行数が多いと手作業は手間なのでCopilotにやってもらったら綺麗にやっていただいた。これから活用したい
-> ユニットテストも書く内容、確認したい内容が明らかで確認しやすいものは書いてもらったりしてます。自分の手で書くよりかなり早い

仕様書・要件書とコードを踏まえて必要なコードの修正とかの壁打ち
-> PDFファイルを画像として分割の上保存してくれるツール(水谷くん作)を活用して画像化(<https://github.com/Wonderplanet/ai-tools/tree/main/ai-context-prep/pdf-to-png-converter>)
-> 画像をContextとして設定すると読み取ってくれる
-> コードとかもContextに含めてAgentモードを使用してmdファイルにまとめてもらう```
```


### Reply 11

**Author:** Daichi Takishima (`U9YULJPUZ`)
**Timestamp:** 2026-01-15 11:52:41 (`1768445561.373109`)

**Message:**

```
「今瞬間TODOリスト」をGemini(LLM, nano banana)に作ってもらって運用してます(html, css, js)。
30分単位でタスクと優先度と対応作業が変化するので「今この瞬間は、このタスクを消化する」のを記載してます。
これで元作業に戻ったとき、思い出すためのスイッチングコスト下げるのに役立ってます。
(あと絵が可愛いので、気持ちが少し安らぐ)
```

**Attachments:**

- `image.png` (image/png, 705250 bytes)
  - Path: [attachments/F0A8SC57ZFG_image.png](attachments/F0A8SC57ZFG_image.png)


### Reply 12

**Author:** Miharu Kimura (`U1RJART4G`)
**Timestamp:** 2026-01-15 11:57:59 (`1768445879.550369`)

**Message:**

```
・Adobe Fireflyのムードボードを使用し、ベンチマークをまとめる
・まとめたベンチマークからAIで合成画像を作ってイメージの精度を上げる
```

**Attachments:**

- `スクリーンショット 2026-01-15 11.54.31.png` (image/png, 104077 bytes)
  - Path: [attachments/F0A8UDGHW90_スクリーンショット_2026-01-15_11.54.31.png](attachments/F0A8UDGHW90_スクリーンショット_2026-01-15_11.54.31.png)


### Reply 13

**Author:** Tomoko Ookawa (`UUMG9SH37`)
**Timestamp:** 2026-01-15 12:12:35 (`1768446755.853529`)

**Message:**

```
・Photoshopスマートオブジェクトのリンク確認ツール作成
・ドンさん作成のモーションチェックツールに機能追加
```


### Reply 14

**Author:** Kazuomi Sawada (`U038MD23A1Z`)
**Timestamp:** 2026-01-15 12:20:00 (`1768447200.938119`)

**Message:**

```
・GitHub Copilotでのテストコード作成・修正とテスト不足箇所の特定
・テスト失敗内容を一括取得して修正効率を上げるエディタ拡張の作成
・プランナーからのプレハブ確認依頼を半自動化するエディタ拡張の作成
・ログイン等の手順をスキップしてUnity上でガシャ演出のみを確認できる専用シーンの構築
・不具合起票のエラー内容や現象からAIを利用した原因箇所の調査
あとは日々の実装ではほぼcopilotを使用しています。
```


### Reply 15

**Author:** Tomoko Yamada (`U2Q2M02NN`)
**Timestamp:** 2026-01-15 12:28:34 (`1768447714.529239`)

**Message:**

```
• 予算の概算を出した時の金額計算ダブルチェック
• 外注から送られてきた発注書の金額計算に間違いがないかの確認
• 普段使っているツールのヘルプの代わり
• UnityやSourceTreeのエラー文の翻訳・要約
スクリーンショットから計算の確認や翻訳をしてくれるので非常に助かってます
```


### Reply 16

**Author:** Sayaka Goto (`UGM7VMAQ7`)
**Timestamp:** 2026-01-15 13:11:55 (`1768450315.031679`)

**Message:**

```
絶賛お知らせのワンポチ生成作成中！！
<https://wonderplanet.atlassian.net/wiki/spaces/~701214a87664f7b8c49649ef41e1dc6e1725b/pages/1440841765/AI>
```


### Reply 17

**Author:** Rira Mochizuki (`U9Z8D704U`)
**Timestamp:** 2026-01-15 14:11:32 (`1768453892.728429`)

**Message:**

```
・Unity組み込みでわからないことがあった時にスクショ付きで質問
・Unityのパーティクル作成でやりたいイメージの数値設定
```


### Reply 18

**Author:** Ryosuke Goto (`U016AL1NM8T`)
**Timestamp:** 2026-01-15 14:48:43 (`1768456123.120339`)

**Message:**

```
・Geminiで下記の記事に記載しているプロンプト入力で文言作成を効率化した
▼ゲーム内お知らせ作る君
```<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1376550916/Gemini>```
▼ゲーム内FAQ作る君
```<https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1376714755/FAQ>```
・リリース後の機能、スキルの仕様要件書作成に活用し、下地を作らせた
```


### Reply 19

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-16 12:33:36 (`1768534416.022249`)

**Message:**

```
GeminiのGemでミッションのマスタデータを作れるかもしれない
&gt; [Gemini Gem] 運営仕様書からミッションのマスタデータ作成をやってみた
&gt; <https://wonderplanet.atlassian.net/wiki/spaces/GLOW/pages/1449328643/Gemini+Gem>
```


### Reply 20

**Author:** Junki Mizutani (`U01SLEEPKQD`)
**Timestamp:** 2026-01-16 12:42:44 (`1768534964.280909`)

**Message:**

```
何やら調子が悪いのでもう少し検証してみます:man-bowing::skin-tone-2:
```

