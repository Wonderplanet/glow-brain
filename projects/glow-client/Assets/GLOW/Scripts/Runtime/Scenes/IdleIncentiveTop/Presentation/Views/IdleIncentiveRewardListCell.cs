using GLOW.Core.Presentation.Components;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public class IdleIncentiveRewardListCell : UICollectionViewCell
    {
        [SerializeField] PlayerResourceIconButtonComponent _icon;

        public void Setup(IdleIncentiveRewardListCellViewModel viewModel)
        {
            var reward = viewModel.Reward;
            _icon.Setup(reward);
        }
    }
}
