using System;
using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-1_図鑑
    /// 　　91-1-3_キャラ図鑑ランク
    /// </summary>
    public class EncyclopediaRewardView : UIView
    {
        [SerializeField] UIText _currentRank;
        [SerializeField] EncyclopediaRewardListComponent _rewardList;
        [SerializeField] Button _receiveAllButton;
        [SerializeField] Button _showEncyclopediaEffectButton;

        public void Setup(EncyclopediaRewardViewModel viewModel,
            Action<EncyclopediaRewardListCellViewModel> onSelectReward,
            Action<EncyclopediaRewardListCellViewModel> onSelectLockReward)
        {
            _rewardList.Setup(viewModel.ReleasedCells, viewModel.LockedCells, onSelectReward, onSelectLockReward);
            _currentRank.SetText(viewModel.CurrentGrade.ToString());

            _receiveAllButton.interactable = viewModel.ReleasedCells.Any(cell => cell.Badge.Value);
            _showEncyclopediaEffectButton.interactable = viewModel.ReleasedCells.Any();
        }
    }
}
