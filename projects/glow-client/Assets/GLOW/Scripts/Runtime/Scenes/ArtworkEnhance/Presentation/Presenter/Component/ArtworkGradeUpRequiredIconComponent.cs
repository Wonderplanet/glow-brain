using System;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter.Component
{
    public class ArtworkGradeUpRequiredIconComponent : UIObject
    {
        [SerializeField] ItemIconComponent _itemIcon;
        [SerializeField] Button _iconButton;
        [SerializeField] UIText _itemAmountText;
        [SerializeField] Color _itemShortageColor;
        [SerializeField] Color _itemLongageColor;

        public void Setup(ArtworkGradeUpRequiredIconViewModel viewModel, Action onIconTapped)
        {
            _itemIcon.Setup(
                viewModel.IconAssetPath,
                viewModel.Rarity,
                viewModel.Amount);

            _itemAmountText.SetColor(_itemLongageColor);
            if (!viewModel.IsEnough) _itemAmountText.SetColor(_itemShortageColor);

            _itemAmountText.SetText(viewModel.Amount.Value.ToString());

            _iconButton.onClick.RemoveAllListeners();
            _iconButton.onClick.AddListener(() => { onIconTapped?.Invoke(); });
        }
    }
}
