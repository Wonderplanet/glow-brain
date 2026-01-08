using System;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattleRaidTotalScoreRewardListCell : UICollectionViewCell
    {
        [SerializeField] AdventBattleRewardResourcesComponent _rewardResourcesComponent;
        [SerializeField] AdventBattleRaidTotalScoreRewardComponent _raidTotalScoreRewardComponent;
        [SerializeField] UIObject _grayOutObject;
        
        public void SetupRaidScoreReward(
            AdventBattleRaidTotalScoreRewardCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onRewardIconTapped)
        {
            _raidTotalScoreRewardComponent.Hidden = false;
            
            _raidTotalScoreRewardComponent.SetScore(viewModel.RewardCondition.ToAdventBattleScore());
            
            _rewardResourcesComponent.SetupRewards(viewModel.Rewards, onRewardIconTapped);

            _grayOutObject.Hidden = !viewModel.DidReceiveReward;
        }
    }
}