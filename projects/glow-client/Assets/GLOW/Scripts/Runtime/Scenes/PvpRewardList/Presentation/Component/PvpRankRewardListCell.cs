using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpRankRewardListCell : UICollectionViewCell
    {
        [SerializeField] PvpRewardResourcesComponent _rewardResourcesComponent;
        [SerializeField] UIText _requiredTotalScoreText;
        [SerializeField] UIText _rankNameText;
        [SerializeField] RankingRankIcon _rankIcon;
        
        public void SetUpTotalPointRewardComponent(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _rewardResourcesComponent.SetUpRewards(viewModels, rewardIconAction);
        }
        
        public void SetUpRequiredTotalScoreText(PvpPoint requiredTotalScore)
        {
            _requiredTotalScoreText.SetText("{0} ~", requiredTotalScore.ToDisplayString());
        }

        public void SetUpRankIcon(PvpRankClassType rankType, PvpRankLevel rankLevel)
        {
            _rankIcon.SetupRankType(rankType);
            _rankIcon.PlayRankTierAnimation(rankLevel);
        }
        
        public void SetUpRankName(PvpRankClassType rankType, PvpRankLevel rankLevel)
        {
            _rankNameText.SetText(rankType.ToDisplayStringWithRankLevel(rankLevel));
        }
    }
}