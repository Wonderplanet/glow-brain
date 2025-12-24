# PvpTop画面（PvP/決闘トップ画面）

## 基本情報

| 項目 | 内容 |
|------|------|
| **画面名** | PvpTop画面（PvP/決闘トップ画面） |
| **画面種別** | メイン画面 |

## 概要

PvP（決闘）コンテンツのメイン画面です。プレイヤーは対戦相手の情報を確認し、選択して決闘を開始できます。現在のランク、スコア、挑戦回数（無料または有料）を確認しながら、マッチメイキングされた複数の対戦相手から相手を選んで対戦に挑むことができます。

## プレイヤーができること

### 決闘を実行する

#### 対戦相手を選択
- 画面に表示された3人の対戦相手から1人を選択
- 相手のユーザー名、アイコン、スコア、パーティユニットを確認可能
- 相手のパーティの総ステータスと対象ユニットの上昇状態を表示
- 相手の詳細情報（ユニット情報ボタン）をモーダルで確認可能

#### 対戦相手をリフレッシュ
- リフレッシュボタンをタップして対戦相手を再マッチメイキング
- クールタイム中は秒数がカウントダウン表示される
- クールタイム終了後にリフレッシュ可能

#### 決闘を開始
- 対戦相手を選択して「決闘開始」ボタンをタップ
- 通常挑戦の場合は確認画面なしで即座にバトル開始
- チケット挑戦の場合は消費確認ダイアログが表示され、確認後にバトル開始
- PvPシーズン終了時刻を超えている場合は自動的にホーム画面に戻る

### 現在の状態を確認する

#### プレイヤーのランク情報
- **現在のスコア**: 累計PvPポイント
- **次のランクアップまでのスコア**: あと何ポイント必要か
- **ランククラス**: プレイヤーの現在のランク階級
- **ランクレベル**: ランク内での細かいレベル表示

#### 挑戦可能状態
- **通常挑戦**: 無料で毎日決まった回数挑戦可能（1日単位でリセット）
- **チケット挑戦**: PvPチケットを消費して追加挑戦
- 挑戦できない状態の場合はボタンがグレーアウト表示される

#### 累積ポイント報酬
- 累積スコアが一定値に達した場合の報酬を表示
- 次に受け取れる報酬までの進捗状況

### 情報を確認する

#### ランキング情報
- ランキングボタンをタップしてランキング画面を表示
- ランキング集計中の場合はトーストメッセージで通知
- シーズン未開始の場合は表示不可

#### 報酬情報
- 報酬リストボタンをタップしてPvP報酬一覧を表示
- シーズン終了時の報酬を確認可能

#### PvPルール説明
- ルール説明ボタン（ヘルプボタン）をタップ
- PvPのルールや仕様についてのチュートリアル情報を表示

#### PvP詳細情報
- PvP詳細ボタンをタップしてPvP情報モーダルを表示
- シーズン終了日時などの詳細情報を確認可能

### パーティを変更する

- 「パーティ編成」ボタンをタップ
- パーティ編成画面へ遷移
- PvP用の最強パーティを編成可能
- 編成完了後に当画面に戻る

## 画面の要素

### ヘッダー部分
- **ユーザー情報エリア**
  - 現在のランククラスアイコン
  - スコア表示
  - 次のランクアップまでのスコア表示

### メイン表示エリア

#### 現在のパーティ表示
- パーティ名が表示される
- パーティ編成ボタン

#### 対戦相手リスト
- **対戦相手カード**（最大3体表示）
  - ユーザー名
  - ユーザーアイコン（キャラクター画像）
  - エンブレムアイコン（設定されている場合）
  - スコア（勝利時に獲得できるポイント）
  - 総スコア（対戦相手の累計スコア）
  - パーティユニット一覧
  - パーティの総ステータス表示
  - 対象ユニット上昇状態フラグ（上昇状態にあるユニットがいる場合は矢印表示）
- **リフレッシュボタン**
  - クールタイム中は秒数を表示（リアルタイムカウントダウン）
  - クールタイム終了後に通常表示に戻る

#### 挑戦状態表示エリア
- **挑戦回数**
  - 通常挑戦の残り回数
  - チケット挑戦の消費チケット数
  - 挑戦可能状態を視覚的に表示

#### 累積ポイント報酬情報
- 次に受け取れる報酬アイコンと必要スコア
- 現在のスコアからあと何ポイント必要かを表示

### ボタンエリア

#### 決闘開始ボタン
- 対戦相手が選択されている場合のみアクティブ
- 挑戦できない状態の場合はグレーアウト
- タップでバトル開始処理を実行

#### 各種情報ボタン
- **ランキングボタン**: ランキング画面へ遷移
- **報酬リストボタン**: PvP報酬一覧を表示
- **PvP詳細ボタン**: PvP情報モーダルを表示
- **ルール説明ボタン**: チュートリアル表示

#### その他のボタン
- **パーティ編成ボタン**: パーティ編成画面へ遷移
- **戻るボタン**: ホーム画面に戻る

## 画面遷移

### この画面への遷移
- **ホーム画面**: PvPバナーをタップ
  - 前回のシーズン結果演出を表示（初回訪問時のみ）
  - 新シーズン開始演出を表示（新シーズン開始時のみ）
  - 累積ポイント報酬受け取り画面を表示（対象者のみ）

### この画面からの遷移
- **PvP決闘画面（バトル）**: 対戦相手を選択して決闘開始ボタンをタップ
  - 通常挑戦の場合は即座にバトル開始
  - チケット挑戦の場合は消費確認ダイアログを経由してバトル開始
  - バトル実行後は「PvP決闘結果画面」へ遷移

- **PvP対戦相手詳細モーダル**: 対戦相手カードの「ユニット情報」ボタンをタップ
  - 対戦相手のパーティ情報を詳細表示

- **ランキング画面**: ランキングボタンをタップ
  - ランキング集計中の場合は表示不可

- **PvP報酬一覧画面**: 報酬リストボタンをタップ

- **PvP情報モーダル**: PvP詳細ボタンをタップ
  - PvPシーズン情報を表示

- **パーティ編成画面**: パーティ編成ボタンをタップ
  - PvP用パーティの編成画面

- **ホーム画面**: 戻るボタンをタップ
  - 画面遷移が途中の場合の移動

## ゲーム仕様・制約事項

### 挑戦システム
- **通常挑戦**
  - 無料で一定回数まで挑戦可能
  - 1日単位でリセット
  - リセット時刻はサーバーマスタデータで管理

- **チケット挑戦**
  - PvPチケットを消費して追加挑戦可能
  - チケット消費時に確認ダイアログを表示
  - 消費確認画面からショップへ直接遷移可能

### 対戦相手リフレッシュ
- クールタイムが設定されている（設定値はサーバー側で管理）
- クールタイム中は秒単位でカウントダウン表示
- 画面を離れても時間経過に基づいてカウントダウンが進む

### スコアシステム
- **獲得ポイント**
  - 対戦に勝利するとスコアを獲得
  - 各対戦相手ごとに異なるポイント設定

- **ランク決定**
  - 累積スコアに基づいてランククラスが決定
  - ランククラスは複数段階のレベルで構成

- **累積ポイント報酬**
  - 累積スコアが一定値に達すると報酬を獲得
  - 複数段階の報酬が設定可能
  - 報酬は自動受け取りまたは手動確認が必要

### PvPシーズン
- **シーズン終了時刻**
  - 各シーズンには終了時刻が設定されている
  - 終了時刻を超えている場合、決闘開始不可
  - 超過時自動的にホーム画面に遷移

- **前シーズン結果**
  - 初回訪問時にのみ演出を表示
  - 二回目以降は表示されない

- **新シーズン開始演出**
  - 新シーズン開始時に演出を表示
  - プレイヤーのランククラスとレベルを表示

### API呼び出しタイミング
- **初回訪問時**: PvpTopUseCase.UpdateAndGetModel()でAPI呼び出し
- **対戦相手リフレッシュ**: PvpTopOpponentUseCase.RefreshMatchUser()でAPI呼び出し
- **決闘開始時**: PvpStartUseCase.StartPvp()でAPI呼び出し

## 技術参考情報

### 画面実装パス
- `Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/`

### 主要コンポーネント

#### Presenter
- `Presentation/Presenter/PvpTopPresenter.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/Presenter/PvpTopPresenter.cs)

#### ViewController
- `Presentation/View/PvpTopViewController.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/View/PvpTopViewController.cs)

#### ViewModel
- `Presentation/ViewModel/PvpTopViewModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/ViewModel/PvpTopViewModel.cs)
- `Presentation/ViewModel/PvpTopOpponentViewModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/ViewModel/PvpTopOpponentViewModel.cs)
- `Presentation/ViewModel/PvpTopNextTotalScoreRewardViewModel.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/ViewModel/PvpTopNextTotalScoreRewardViewModel.cs)

#### UseCase
- `Domain/UseCase/PvpTopUseCase.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/UseCase/PvpTopUseCase.cs)
- `Domain/UseCase/PvpTopOpponentUseCase.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/UseCase/PvpTopOpponentUseCase.cs)
- `Domain/UseCase/PvpStartUseCase.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/UseCase/PvpStartUseCase.cs)
- `Domain/UseCase/GetPvpTopRankingStateUseCase.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/UseCase/GetPvpTopRankingStateUseCase.cs)

#### ModelFactory
- `Domain/ModelFactories/PvpTopModelFactory.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/ModelFactories/PvpTopModelFactory.cs)
- `Domain/ModelFactories/PvpTopOpponentModelFactory.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Domain/ModelFactories/PvpTopOpponentModelFactory.cs)

#### Translator
- `Presentation/Translator/PvpTopViewModelTranslator.cs` (projects/glow-client/Assets/GLOW/Scripts/Runtime/Scenes/PvpTop/Presentation/Translator/)

### PvpTopViewModelの構造

| プロパティ | 説明 |
|-----------|------|
| `SysPvpSeasonId` | 現在のPvPシーズンID |
| `PvpTopRankingState` | ランキング表示状態（集計中/開催中/未開始） |
| `PvpTopUserState` | プレイヤーの状態（スコア、ランク、挑戦可能状態） |
| `RemainingTimeSpan` | PvPシーズン終了までの残り時間 |
| `OpponentViewModels` | 対戦相手情報のリスト |
| `PartyName` | 現在選択中のパーティ名 |
| `PvpOpponentRefreshCoolTime` | 対戦相手リフレッシュのクールタイム（秒） |
| `HasInGameSpecialRuleUnitStatus` | 対象ユニットの上昇状態がある場合のフラグ |
| `PvpTopNextTotalScoreRewardViewModel` | 次の累積ポイント報酬情報 |

### PvpTopOpponentViewModelの構造

| プロパティ | 説明 |
|-----------|------|
| `UserId` | 対戦相手のユーザーID |
| `UserName` | 対戦相手のユーザー名 |
| `CharacterIconAssetPath` | 対戦相手のメインユニットアイコン |
| `EmblemIconAssetPath` | 対戦相手のエンブレムアイコン |
| `Point` | 勝利時に獲得できるスコア（この相手に勝つと得られるポイント） |
| `TotalPoint` | 対戦相手の累計スコア |
| `PvpUserRankStatus` | 対戦相手のランク情報 |
| `PartyUnits` | 対戦相手のパーティユニット一覧 |
| `TotalPartyStatus` | パーティの総ステータス |
| `TotalPartyStatusUpperArrowFlag` | ステータス上昇状態フラグ |

### 使用しているDBデータ

#### マスタデータ

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| MstPvp.Id<br>MstCharacter.AssetKey<br>MstCharacter.RoleType<br>MstCharacter.Color<br>MstCharacter.Rarity | mst_pvps.id<br>mst_units.asset_key<br>mst_units.role_type<br>mst_units.color<br>mst_units.rarity | PvP情報<br>キャラクターのアイコン画像<br>キャラクターの職業<br>キャラクターの属性色<br>キャラクターのレアリティ |
| MstEmblem.AssetKey | mst_emblems.asset_key | エンブレムのアイコン画像 |
| MstInGameSpecialRule.RuleType<br>MstInGameSpecialRule.RuleValue | mst_in_game_special_rules.rule_type<br>mst_in_game_special_rules.rule_value | PvPの特別ルール種別<br>ルール値（ユニットステータス上昇グループID） |
| MstInGameSpecialRuleUnitStatus.* | mst_in_game_special_rule_unit_statuses.* | 特別ルールによるユニット上昇ステータス |
| MstUnitEncyclopediaEffect.EffectType<br>MstUnitEncyclopediaEffect.Value | mst_unit_encyclopedia_effects.effect_type<br>mst_unit_encyclopedia_effects.value | 図鑑効果の種別（HP/攻撃力/回復力）<br>図鑑効果の値 |
| MstPvpRewardGroup.RewardCategory<br>MstPvpRewardGroup.ConditionValue<br>MstPvpRewardGroup.Rewards | mst_pvp_reward_groups.reward_category<br>mst_pvp_reward_groups.condition_value<br>mst_pvp_reward_groups.rewards | 報酬カテゴリ（累積スコア報酬）<br>報酬の達成条件（必要スコア）<br>報酬内容 |
| MstConfig.PvpTopApiRequestCoolTimeMinute<br>MstConfig.PvpOpponentRefreshCoolTimeSeconds | mst_configs.pvp_top_api_request_cool_time_minute<br>mst_configs.pvp_opponent_refresh_cool_time_seconds | API呼び出しクールタイム（分）<br>対戦相手リフレッシュクールタイム（秒） |

#### ユーザーデータ（GameFetch）

| クライアント定義 | サーバー定義 | クライアント説明 |
|----------------|-------------|----------------|
| SysPvpSeasonModel.Id<br>SysPvpSeasonModel.EndAt | sys_pvp_seasons.id<br>sys_pvp_seasons.end_at | 現在のPvPシーズンID<br>PvPシーズン終了日時 |
| UserPvpStatusModel.MaxReceivedTotalScore | user_pvp_statuses.max_received_total_score | 累積スコア報酬で受け取った最大スコア |
| UserUnitModels | user_units.* | プレイヤーが所有しているユニット |
| UserPartyModel | user_parties.* | プレイヤーが編成したパーティ |
| UsrEncyclopediaEffects | usr_encyclopedia_effects.* | プレイヤーが習得した図鑑効果 |

### 呼び出しているAPI

#### 画面読み込み時
- **POST `/api/pvp/top`**: PvpTopUseCase.UpdateAndGetModel()
  - パラメータなし
  - 用途: 対戦相手一覧の取得、プレイヤーのPvP状態取得、前シーズン結果の取得

#### 対戦相手リフレッシュボタンタップ時
- **POST `/api/pvp/change_opponent`**: PvpTopOpponentUseCase.RefreshMatchUser()
  - パラメータなし
  - 用途: マッチメイキングの再実行、対戦相手の変更

#### 決闘開始ボタンタップ時
- **POST `/api/pvp/start`**: PvpStartUseCase.StartPvp()
  - パラメータ:
    - `sysPvpSeasonId`: PvPシーズンID
    - `isUseItem`: 0=通常挑戦、1=チケット挑戦
    - `opponentMyId`: 対戦相手のユーザーID
    - `partyNo`: パーティ番号
    - `inGameBattleLog`: バトル開始時のパーティ情報ログ
  - 用途: PvPバトルの開始、対戦相手情報の確定

#### ランキング表示時
- **GET `/api/pvp/ranking?isPreviousSeason={bool}`**: PvpRankingUseCase.GetPvpRanking()
  - パラメータ: isPreviousSeason（現シーズン=false、前シーズン=true）
  - 用途: ランキング情報の取得
