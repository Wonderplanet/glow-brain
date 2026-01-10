using System;
using Cysharp.Text;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.Component;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ValueObject;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-5_報酬
    /// 　　44-5-1_報酬一覧画面
    /// </summary>
    public class AdventBattleRewardListView : UIView
    {
        [SerializeField] UIToggleableComponentGroup _twoToggleableComponentGroup;
        [SerializeField] UIToggleableComponentGroup _threeToggleableComponentGroup;
        [SerializeField] AdventBattlePersonalRewardListComponent _personalRankingRewardListComponent;
        [SerializeField] AdventBattlePersonalRewardListComponent _personalRankRewardListComponent;
        [SerializeField] AdventBattleRaidTotalScoreRewardListComponent _raidTotalScoreRewardListComponent;
        [SerializeField] UIText _timeText;
        [FormerlySerializedAs("_descriptiontext")] [SerializeField] UIText _descriptionText;

        public void SetupScoreChallengeRewardListView(
            AdventBattleRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction,
            AdventBattleRewardListTabType tabType)
        {
            _threeToggleableComponentGroup.Hidden = true;
            _twoToggleableComponentGroup.Hidden = false;
            _twoToggleableComponentGroup.SetToggleOn(tabType.ToString());
            _descriptionText.SetText(GetReceiveRewardDescriptionText(tabType));
            
            switch(tabType)
            {
                case AdventBattleRewardListTabType.Ranking:
                    SetupPersonalRankingRewardListView(viewModel, rewardIconAction);
                    break;
                case AdventBattleRewardListTabType.Rank:
                    SetupPersonalRankRewardListView(viewModel, rewardIconAction);
                    break;
                default:
                    break;
            }
        }
        
        public void SetupRaidRewardListView(
            AdventBattleRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction,
            AdventBattleRewardListTabType tabType)
        {
            _twoToggleableComponentGroup.Hidden = true;
            _threeToggleableComponentGroup.Hidden = false;
            _threeToggleableComponentGroup.SetToggleOn(tabType.ToString());
            _descriptionText.SetText(GetReceiveRewardDescriptionText(tabType));
            
            switch(tabType)
            {
                case AdventBattleRewardListTabType.Ranking:
                    SetupPersonalRankingRewardListView(viewModel, rewardIconAction);
                    break;
                case AdventBattleRewardListTabType.Rank:
                    SetupPersonalRankRewardListView(viewModel, rewardIconAction);
                    break;
                case AdventBattleRewardListTabType.Raid:
                    SetupRaidRewardListView(viewModel, rewardIconAction);
                    break;
            }
        }

        public void SetupRemainingTime(RemainingTimeSpan timeSpan)
        {
            _timeText.SetText(TimeSpanFormatter.Format(
                timeSpan.Value, 
                "",
                "",
                22));
        }
        
        void SetupPersonalRankingRewardListView(
            AdventBattleRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _personalRankingRewardListComponent.Hidden = false;
            _personalRankRewardListComponent.Hidden = true;
            _raidTotalScoreRewardListComponent.Hidden = true;
            
            _personalRankingRewardListComponent.SetupPersonalRewardList(
                viewModel.PersonalRankingRewardCellViewModels,
                rewardIconAction);
        }
        
        void SetupPersonalRankRewardListView(
            AdventBattleRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _personalRankingRewardListComponent.Hidden = true;
            _personalRankRewardListComponent.Hidden = false;
            _raidTotalScoreRewardListComponent.Hidden = true;
            
            _personalRankRewardListComponent.SetupPersonalRewardList(
                viewModel.PersonalRankRewardCellViewModels,
                rewardIconAction);
        }
        
        void SetupRaidRewardListView(
            AdventBattleRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _personalRankingRewardListComponent.Hidden = true;
            _personalRankRewardListComponent.Hidden = true;
            _raidTotalScoreRewardListComponent.Hidden = false;
            
            _raidTotalScoreRewardListComponent.SetupRaidTotalScoreRewardList(
                viewModel.RaidTotalScoreRewardCellViewModels,
                rewardIconAction);
        }
        
        string GetReceiveRewardDescriptionText(AdventBattleRewardListTabType tabType)
        {
            switch(tabType)
            {
                case AdventBattleRewardListTabType.Ranking:
                    return "報酬は開催期間終了後にメールBOXに届きます。";
                case AdventBattleRewardListTabType.Rank:
                    return "各ランクに到達することで報酬を獲得できます。";
                case AdventBattleRewardListTabType.Raid:
                    return "目標の協力スコア達成時に各報酬を獲得できます。";
                default:
                    return "";
            }
        }
    }
}