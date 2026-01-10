using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRewardList.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattlePersonalRewardListCell : UICollectionViewCell
    {
        [SerializeField] AdventBattleRewardResourcesComponent _rewardResourcesComponent;
        [SerializeField] AdventBattlePersonalScoreRewardComponent _personalScoreRewardComponent;
        [SerializeField] UIObject _grayOutObject;
        [SerializeField] UIObject _rankingCellFirstRank;
        [SerializeField] UIObject _rankingCellSecondRank;
        [SerializeField] UIObject _rankingCellThirdRank;
        [SerializeField] UIObject _commonRankingCell;
        
        
        AdventBattleScoreRankLevel _rankLevel = AdventBattleScoreRankLevel.Empty;
        
        protected override void Awake()
        {
            base.Awake();
            
            _personalScoreRewardComponent.IsVisible = true;
        }
        
        protected override void OnEnable()
        {
            // SetTriggerを呼び出すために必要
            // CollectionViewのリサイクル時にSetTriggerを実行する場合だとCell自体が非アクティブになっている状態のため
            // 正しく再生されない。
            // そのため、OnEnableでアクティブになった瞬間にSetTriggerを呼ぶようにすることで実行する。
            base.OnEnable();
            
            _personalScoreRewardComponent.PlayRankTierAnimation(_rankLevel);
            _rankLevel = AdventBattleScoreRankLevel.Empty;
        }

        public void SetupPersonalScoreReward( 
            IAdventBattlePersonalCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onRewardIconTapped)
        {
            _personalScoreRewardComponent.IsVisible = true;
            
            if (viewModel.RewardCategory == AdventBattleRewardCategory.Rank)
            {
                var scoreRankRewardCellViewModel = viewModel as AdventBattleScoreRankRewardCellViewModel 
                                                   ?? AdventBattleScoreRankRewardCellViewModel.Empty;
                SetupScoreRankReward(scoreRankRewardCellViewModel, onRewardIconTapped);
                SetUpPersonalRewardListCellBase(AdventBattleRankingRank.Empty);
                
                // onEnableでアニメーションを再生するために保持
                _rankLevel = scoreRankRewardCellViewModel.RewardRankLevel;
            }
            else if (viewModel.RewardCategory == AdventBattleRewardCategory.Ranking)
            {
                var singleRankingRewardCellViewModel = viewModel as AdventBattleSingleRankingRewardCellViewModel 
                                                       ?? AdventBattleSingleRankingRewardCellViewModel.Empty;
                
                var intervalRankingRewardCellViewModel = viewModel as AdventBattleIntervalRankingRewardCellViewModel 
                                                         ?? AdventBattleIntervalRankingRewardCellViewModel.Empty;
                
                if(!singleRankingRewardCellViewModel.IsEmpty())
                {
                    SetupSingleRankingReward(singleRankingRewardCellViewModel, onRewardIconTapped);
                    SetUpPersonalRewardListCellBase(singleRankingRewardCellViewModel.RankingRank);
                }
                else if(!intervalRankingRewardCellViewModel.IsEmpty())
                {
                    SetupIntervalRankingReward(intervalRankingRewardCellViewModel, onRewardIconTapped);
                    SetUpPersonalRewardListCellBase(intervalRankingRewardCellViewModel.RankingRankUpper);
                }
                
                _grayOutObject.IsVisible = false;
            }
        }

        void SetupSingleRankingReward(
            AdventBattleSingleRankingRewardCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onRewardIconTapped)
        {
            _personalScoreRewardComponent.SetRankingNumber(AdventBattleRankingRank.Empty, viewModel.RankingRank);
            _personalScoreRewardComponent.HideRankIcon();
            _personalScoreRewardComponent.HideScore();
            _personalScoreRewardComponent.SetUpRankingNumberVisible(viewModel.RankingRank);
            _rewardResourcesComponent.SetupRewards(viewModel.Rewards, onRewardIconTapped);
        }
        
        void SetupIntervalRankingReward(
            AdventBattleIntervalRankingRewardCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onRewardIconTapped)
        {
            _personalScoreRewardComponent.SetRankingNumber(viewModel.RankingRankUpper, viewModel.RankingRankLower);
            _personalScoreRewardComponent.HideRankIcon();
            _personalScoreRewardComponent.HideScore();
            _personalScoreRewardComponent.SetUpRankingNumberVisible(viewModel.RankingRankUpper);
            _rewardResourcesComponent.SetupRewards(viewModel.Rewards, onRewardIconTapped);
        }
        
        void SetupScoreRankReward(
            AdventBattleScoreRankRewardCellViewModel viewModel,
            Action<PlayerResourceIconViewModel> onRewardIconTapped)
        {
            _personalScoreRewardComponent.SetRankName(viewModel.RewardRankType, viewModel.RewardRankLevel);
            _personalScoreRewardComponent.SetRankRequiredScore(viewModel.RewardRankLowerScore);
            _personalScoreRewardComponent.SetRankIconType(viewModel.RewardRankType);
            _personalScoreRewardComponent.PlayRankTierAnimation(viewModel.RewardRankLevel);
            _rewardResourcesComponent.SetupRewards(viewModel.Rewards, onRewardIconTapped);
            _personalScoreRewardComponent.SetUpRankingNumberVisible(AdventBattleRankingRank.Empty);
            _grayOutObject.IsVisible = viewModel.DidReceiveReward;
        }
        
        void SetUpPersonalRewardListCellBase(AdventBattleRankingRank rankingRank)
        {
            _rankingCellFirstRank.IsVisible = rankingRank.IsFirstRank();
            _rankingCellSecondRank.IsVisible = rankingRank.IsSecondRank();
            _rankingCellThirdRank.IsVisible = rankingRank.IsThirdRank();
            _commonRankingCell.IsVisible = rankingRank.IsEmpty() || rankingRank.IsLowerFourth();
        }
    }
}