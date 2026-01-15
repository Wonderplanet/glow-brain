using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeStageInfoSpeedAttackReward : UIObject
    {
        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;
        [SerializeField] UIText _clearTimeText;

        public void Setup(PlayerResourceIconViewModel viewModel)
        {
            _playerResourceIconComponent.Setup(
                viewModel.AssetPath,
                viewModel.RarityFrameType,
                viewModel.Rarity,
                viewModel.Amount,
                viewModel.IsAcquired,
                StageClearTime.Empty,
                viewModel.RewardCategoryLabel);
            _clearTimeText.SetText("{0}秒以内にクリア", viewModel.ClearTime.ToStringSeconds());
        }
    }
}
