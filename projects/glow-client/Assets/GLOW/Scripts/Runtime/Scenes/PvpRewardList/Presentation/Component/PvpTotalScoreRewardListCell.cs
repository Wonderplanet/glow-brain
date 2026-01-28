using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRewardList.Presentation.Component
{
    public class PvpTotalScoreRewardListCell : UICollectionViewCell
    {
        [SerializeField] PvpRewardResourcesComponent _rewardResourcesComponent;
        [SerializeField] UIText _requiredTotalScoreText;
        [SerializeField] UIObject _receivedGrayoutObject;
        
        public void SetUpTotalPointRewardComponent(
            IReadOnlyList<PlayerResourceIconViewModel> viewModels,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _rewardResourcesComponent.SetUpRewards(viewModels, rewardIconAction);
        }
        
        public void SetUpRequiredTotalScoreText(PvpPoint requiredTotalScore)
        {
            _requiredTotalScoreText.SetText(requiredTotalScore.ToDisplayString());
        }
        
        public void SetUpReceivedObject(bool isReceived)
        {
            _receivedGrayoutObject.IsVisible = isReceived;
        }
    }
}