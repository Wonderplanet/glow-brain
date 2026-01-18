using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.DebugStageDetail.Domain;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public class DebugStageDetailView : UIView
    {
        [Header("最上部")]
        [SerializeField] Text _questNameAndDifficyltyText;

        [Header("概要")]
        [SerializeField] Image _summaryButtonImage;
        [SerializeField] ScrollRect _summaryScrollRect;
        [SerializeField] Text _summaryText;

        [Header("使いまわし")]
        [SerializeField] DebugStageDetailButtonComponent _komaButtonComponent;
        [SerializeField] RectTransform _buttonContentRect;

        [SerializeField] DebugStageDetailContentComponent _rewardContentComponent;
        [SerializeField] RectTransform _rewardContentRect;

        List<DebugStageDetailButtonComponent> _instancedButtons = new List<DebugStageDetailButtonComponent>();
        List<DebugStageDetailContentComponent> _instancedContents = new List<DebugStageDetailContentComponent>();

        public void SetTopText(string questName, string difficulty)
        {
            _questNameAndDifficyltyText.text = $"{questName} - {difficulty}";
        }

        public void SetSummaryText(DebugStageQuestSummaryModel summary)
        {
            _summaryText.text = "";
            _summaryText.text += $"==クエスト名==\n";
            _summaryText.text += $"{summary.QuestName.Value}\n\n";

            _summaryText.text += $"==ノーマルID_normal==\n";
            _summaryText.text += $"{summary.NormalMasterDataId.Value}\n\n";

            _summaryText.text += $"==ノーマルID_hard==\n";
            _summaryText.text += $"{summary.HardQuestMstId.Value}\n\n";

            _summaryText.text += $"==ノーマルID_extra==\n";
            _summaryText.text += $"{summary.ExtraQuestMstId.Value}\n\n";

            _summaryText.text += $"==クエスト種別==\n";
            _summaryText.text += $"{summary.QuestType}\n\n";

            _summaryText.text += $"==開始日時==\n";
            _summaryText.text += $"{summary.ToStartString()}\n\n";

            if (summary.EndAt.IsUnlimitedEndAt)
            {
                _summaryText.text += $"==終了日時==\n\n";
            }
            else
            {
                _summaryText.text += $"==終了日時==\n";
                _summaryText.text += $"{summary.ToEndString()}\n\n";
            }

            _summaryText.text += "==フレーバーテキスト==\n";
            _summaryText.text += $"{summary.FlavorText.Value}\n\n";

            // イベント専用情報
            if (summary.QuestType == DebugStageDetailQuestType.Event)
            {
                _summaryText.text += "==イベントクエストTOPキャラ==\n";
                foreach (var character in summary.EventUnitModels)
                {
                    _summaryText.text += $"--クエストトップ表示キャラID--\n";
                    _summaryText.text += $"{character.UnitAssetKey.Value}\n\n";
                    _summaryText.text += $"--キャラ名--\n";
                    _summaryText.text += $"{character.UnitName.Value}\n\n";
                    for (int i = 0; i < character.SpeechBalloonTexts.Count; i++)
                    {
                        _summaryText.text += $"--セリフ{i + 1}--\n";
                        _summaryText.text += $"{character.SpeechBalloonTexts[i].Value}\n\n";
                    }
                }
            }

            //pvp専用情報
            if (summary.QuestType == DebugStageDetailQuestType.Pvp)
            {
                var pvp = summary.PvpStatus;
                _summaryText.text += "==PVPクエスト専用情報==\n";
                _summaryText.text += "--ランキング--\n";
                _summaryText.text += $"開催？...{pvp.RankingOpeningString()}\n";
                _summaryText.text += $"参加最低ランク...{pvp.MinPvpRankClassString()}\n";
                _summaryText.text += $"専用報酬...{pvp.RewardString()}\n";
                _summaryText.text += "\n--1日の挑戦上限--\n";
                _summaryText.text += $"フリー...{pvp.MaxDailyChallengeCount.Value}\n";
                _summaryText.text += $"チケット...{pvp.MaxDailyItemChallengeCount.Value}\n";
            }
        }

        public void SetStageInfoText(
            IReadOnlyList<DebugStageDetailElementStageInfoUseCaseModel> stageInfos,
            Action<StageNumber> select)
        {
            foreach (var info in stageInfos)
            {
                var instancedButton = Instantiate(_komaButtonComponent, _buttonContentRect);
                // タブボタン設定
                instancedButton.ButtonImage.color = Color.gray;
                instancedButton.ButtonText.text = $"{info.BaseInfo.StageNumber.Value}話";
                instancedButton.StageNumber = info.BaseInfo.StageNumber;
                instancedButton.Button.onClick.AddListener(() => select(info.BaseInfo.StageNumber));

                var instancedContent = Instantiate(_rewardContentComponent, _rewardContentRect);
                // コンテンツ設定
                instancedContent.gameObject.SetActive(false);
                instancedContent.StageNumber = info.BaseInfo.StageNumber;

                instancedContent.Text.text = "";
                instancedContent.Text.text += $"///////基本情報///////\n";
                instancedContent.Text.text += $"ステージ名...{info.BaseInfo.NameString()}\n";
                instancedContent.Text.text += $"スタミナ...{info.BaseInfo.ConsumeStamina.Value}\n";
                instancedContent.Text.text += $"難易度...{info.BaseInfo.Difficulty}\n";
                instancedContent.Text.text += $"通常BGM...{info.BaseInfo.NormalBGMAssetKey.Value}\n";
                instancedContent.Text.text += $"ボスBGM...{info.BaseInfo.BossBGMAssetKey.Value}\n";
                instancedContent.Text.text += $"味方ゲートID...{info.BaseInfo.PlayerOutpostAssetKey.Value}\n";
                instancedContent.Text.text += $"敵ゲートID...{info.BaseInfo.MstEnemyOutpostId.Value}\n";
                instancedContent.Text.text += $"敵ゲートasset(ゲート本体)...{info.BaseInfo.EnemyOutpostAssetKey.Value}\n";
                instancedContent.Text.text += $"敵ゲートasset(原画)...{info.BaseInfo.EnemyOutpostArtworkAssetKey.Value}\n";
                instancedContent.Text.text += $"敵ゲートHP...{info.BaseInfo.HpString()}\n";
                instancedContent.Text.text += $"\n";

                instancedContent.Text.text += $"///////報酬設計///////\n";
                instancedContent.Text.text += $"--ステージ報酬--\n";
                instancedContent.Text.text += $"クリアコイン...{info.Rewards.ClearCoin.Value}\n";
                instancedContent.Text.text += $"経験値...{info.Rewards.Exp.Value}\n";
                instancedContent.Text.text += $"原画のかけら確率...{info.Rewards.DropPercentage.Value}\n";
                instancedContent.Text.text += $"原画のかけら種...{info.Rewards.FragmentNumsString()}";
                instancedContent.Text.text += $"\n\n";

                //報酬設定
                instancedContent.Text.text += $"--スピードアタック報酬--\n";
                foreach (var reward in info.Rewards.SpeedAttackRewardModels)
                {
                    instancedContent.Text.text += $"クリアタイム...{reward.Time.Value}\n";
                    instancedContent.Text.text += $"リソースtype...{reward.ResourceType}\n";
                    instancedContent.Text.text += $"アイテムID...{reward.ResourceId.Value}\n";
                    instancedContent.Text.text += $"アイテム名...{reward.PlayerResourceName.Value}\n";
                    instancedContent.Text.text += $"ドロップ数...{reward.ResourceAmount.Value}\n";
                    instancedContent.Text.text += $"----\n";

                }
                instancedContent.Text.text += $"\n";

                instancedContent.Text.text += $"--ドロップアイテム報酬--\n";
                foreach (var reward in info.Rewards.RewardItems)
                {
                    instancedContent.Text.text += $"ドロップ区分...{reward.RewardCategory}\n";
                    instancedContent.Text.text += $"リソースtype...{reward.ResourceType}\n";
                    instancedContent.Text.text += $"アイテムID...{reward.ResourceId.Value}\n";
                    instancedContent.Text.text += $"アイテム名...{reward.PlayerResourceName.Value}\n";
                    instancedContent.Text.text += $"ドロップ数...{reward.ResourceAmount.Value}\n";
                    instancedContent.Text.text += $"ドロップ率...{reward.DropPercentage.Value}\n";
                    instancedContent.Text.text += $"----\n";
                }
                instancedContent.Text.text += $"\n";

                instancedContent.Text.text += $"///////コマ設計///////\n";
                foreach (var koma in info.Komas.Elements)
                {
                    //コマ基本
                    instancedContent.Text.text += $"--コマ--\n";
                    instancedContent.Text.text += $"行数...{koma.Row}\n";
                    instancedContent.Text.text += $"行パターンID...{koma.KomaLineLayoutAssetKey}\n";
                    instancedContent.Text.text += $"コマ数...{koma.KomaCount}\n";
                    instancedContent.Text.text += $"コマ高さ...{koma.Height}\n";
                    instancedContent.Text.text += $"コマ幅1...{koma.Koma1.Width}\n";
                    if (!koma.Koma2.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ幅2...{koma.Koma2.Width}\n";
                    }
                    if (!koma.Koma3.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ幅3...{koma.Koma3.Width}\n";
                    }
                    if (!koma.Koma4.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ幅4...{koma.Koma4.Width}\n";
                    }
                    instancedContent.Text.text += $"コマ背景...{koma.KomaBackgroundAssetKey.Value}\n";
                    instancedContent.Text.text += $"\n";

                    // コマ効果
                    instancedContent.Text.text += $"コマ効果1...{koma.Koma1.EffectType}\n";
                    instancedContent.Text.text += $"効果時間...{koma.Koma1.KomaEffectString()}\n";
                    instancedContent.Text.text += $"\n";

                    if (!koma.Koma2.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ効果2...{koma.Koma2.EffectType}\n";
                        instancedContent.Text.text += $"効果時間...{koma.Koma2.KomaEffectString()}\n";
                        instancedContent.Text.text += $"\n";
                    }

                    if (!koma.Koma3.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ効果3...{koma.Koma3.EffectType}\n";
                        instancedContent.Text.text += $"効果時間...{koma.Koma3.KomaEffectString()}\n";
                        instancedContent.Text.text += $"\n";
                    }

                    if (!koma.Koma4.IsEmpty())
                    {
                        instancedContent.Text.text += $"コマ効果4...{koma.Koma4.EffectType}\n";
                        instancedContent.Text.text += $"効果時間...{koma.Koma4.KomaEffectString()}\n";
                        instancedContent.Text.text += $"\n";
                    }
                }

                // 操作用キャッシュ追加
                _instancedButtons.Add(instancedButton);
                _instancedContents.Add(instancedContent);
            }
        }

        public void ShowSummary()
        {
            _summaryButtonImage.color =new Color32(44, 64, 109, 255);
            foreach (var instanced in _instancedButtons)
            {
                instanced.ButtonImage.color = Color.gray;
            }

            _summaryScrollRect.gameObject.SetActive(true);
            foreach (var instanced in _instancedContents)
            {
                instanced.gameObject.SetActive(false);
            }
        }

        public void ShowStageInfo(StageNumber stageNumber)
        {
            _summaryButtonImage.color = Color.gray;
            foreach (var instanced in _instancedButtons)
            {
                instanced.ButtonImage.color = instanced.StageNumber == stageNumber
                    ? new Color32(44, 64, 109, 255)
                    : Color.gray;
            }

            _summaryScrollRect.gameObject.SetActive(false);
            foreach (var instanced in _instancedContents)
            {
                instanced.gameObject.SetActive(instanced.StageNumber == stageNumber);
            }
        }

        protected override void OnDestroy()
        {
            _instancedButtons.Clear();
            _instancedContents.Clear();
        }
    }
}
