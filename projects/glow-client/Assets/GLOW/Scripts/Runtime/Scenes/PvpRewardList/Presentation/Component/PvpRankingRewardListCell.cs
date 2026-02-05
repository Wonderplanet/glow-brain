using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpRankingRewardListCell : UICollectionViewCell
    {
        [SerializeField] PvpRewardResourcesComponent _rewardResourcesComponent;
        [SerializeField] UIText _rankingRankText;
        [SerializeField] UIObject _rankingCellFirstRank;
        [SerializeField] UIObject _rankingCellSecondRank;
        [SerializeField] UIObject _rankingCellThirdRank;
        [SerializeField] UIObject _commonRankingCell;
        
        public void SetUpRankingRewardComponent(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _rewardResourcesComponent.SetUpRewards(viewModels, rewardIconAction);
        }

        public void SetUpRankingCell(PvpRankingRank rankingRank)
        {
            _rankingCellFirstRank.IsVisible = rankingRank.IsFirstRank();
            _rankingCellSecondRank.IsVisible = rankingRank.IsSecondRank();
            _rankingCellThirdRank.IsVisible = rankingRank.IsThirdRank();
            _commonRankingCell.IsVisible = rankingRank.IsLowerFourth();
        }
        
        public void SetUpRankingRankText(PvpRankingRank rankingRank, string rankText)
        {
            if (rankingRank.IsLowerFourth())
            {
                _rankingRankText.IsVisible = true;
                _rankingRankText.SetText(rankText);
            }
            else
            {
                _rankingRankText.IsVisible = false;
            }
        }
    }
}