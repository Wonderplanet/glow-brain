using GLOW.Core.Presentation.Components;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Views
{
    public class SpecialAttackInfoView : UIView
    {
        const float MaxScrollSize = 654;
        const float CellSize = 51;
        const float CellSpacing = 16;

        [SerializeField] UICollectionView _rankCollectionView;

        [SerializeField] UIText _specialAttackNameText;
        [SerializeField] UIText _specialAttackDescriptionText;
        [SerializeField] UIText _attackComboCycleText;

        public UICollectionView RankCollectionView => _rankCollectionView;

        public void Setup(SpecialAttackInfoViewModel viewModel)
        {
            _specialAttackNameText.SetText(viewModel.Name.Value);
            _specialAttackDescriptionText.SetText(viewModel.Description.Value);
            _attackComboCycleText.SetText(viewModel.CoolTime.ToCoolTimeString());

            var viewHeight = Mathf.Min(MaxScrollSize, viewModel.RankViewModelList.Count * (CellSize + CellSpacing) - CellSpacing);
            _rankCollectionView.RectTransform.SetSizeWithCurrentAnchors(RectTransform.Axis.Vertical, viewHeight);
        }
    }
}
