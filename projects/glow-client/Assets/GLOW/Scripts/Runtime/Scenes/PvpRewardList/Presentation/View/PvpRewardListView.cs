using System;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.PvpRewardList.Presentation.Component;
using GLOW.Scenes.PvpRewardList.Presentation.Enum;
using GLOW.Scenes.PvpRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.View
{
    /// <summary>
    /// 043-01_報酬一覧画面
    /// </summary>
    public class PvpRewardListView : UIView
    {
        [SerializeField] UIToggleableComponentGroup _threeToggleableComponentGroup;
        [SerializeField] PvpRankingRewardListComponent _pvpRankingRewardListComponent;
        [SerializeField] PvpRankRewardListComponent _pvpRankRewardListComponent;
        [SerializeField] PvpTotalScoreRewardListComponent _pvpTotalScoreRewardListComponent;
        [SerializeField] UIText _timeText;
        [SerializeField] UIText _afterPvpText;
        [SerializeField] UIText _totalPointText;

        public void InitializeRewardListView()
        {
            _pvpRankingRewardListComponent.Initialize();
            _pvpRankRewardListComponent.Initialize();
            _pvpTotalScoreRewardListComponent.Initialize();
        }
        
        public void SetUpRewardListView(
            PvpRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction,
            PvpRewardListTabType tabType)
        {
            _threeToggleableComponentGroup.IsVisible = true;
            _threeToggleableComponentGroup.SetToggleOn(tabType.ToString());
            
            _pvpRankingRewardListComponent.IsVisible = PvpRewardListTabType.Ranking == tabType;
            _pvpRankRewardListComponent.IsVisible = PvpRewardListTabType.RankClass == tabType;
            _pvpTotalScoreRewardListComponent.IsVisible = PvpRewardListTabType.TotalScore == tabType;
            
            // 期間後受け取りテキスト
            _afterPvpText.IsVisible = PvpRewardListTabType.TotalScore != tabType;
            // 達成時受け取りテキスト
            _totalPointText.IsVisible = PvpRewardListTabType.TotalScore == tabType;
            
            switch(tabType)
            {
                case PvpRewardListTabType.Ranking:
                    SetUpRankingRewardListView(viewModel, rewardIconAction);
                    break;
                case PvpRewardListTabType.RankClass:
                    SetUpRankRewardListView(viewModel, rewardIconAction);
                    break;
                case PvpRewardListTabType.TotalScore:
                    SetUpTotalPointRewardListView(viewModel, rewardIconAction);
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
        
        void SetUpRankingRewardListView(
            PvpRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpRankingRewardListComponent.SetUpRankingRewardList(
                viewModel.RankingRewardCellViewModels,
                rewardIconAction);
        }

        void SetUpRankRewardListView(
            PvpRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpRankRewardListComponent.SetUpRankRewardList(
                viewModel.PointRankRewardCellViewModels,
                rewardIconAction);
        }
        
        void SetUpTotalPointRewardListView(
            PvpRewardListViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _pvpTotalScoreRewardListComponent.SetUpTotalPointRewardList(
                viewModel.TotalPointRewardCellViewModels,
                rewardIconAction);
        }
    }
}