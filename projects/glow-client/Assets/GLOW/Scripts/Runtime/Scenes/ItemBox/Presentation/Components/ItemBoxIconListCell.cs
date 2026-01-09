using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class ItemBoxIconListCell : UICollectionViewCell
    {
        [SerializeField] ItemIconComponent _itemIconComponent;
        [SerializeField] Button _button;

        public void Setup(ItemIconViewModel viewModel)
        {
            _itemIconComponent.Setup(viewModel.ItemIconAssetPath, viewModel.Rarity, viewModel.Amount);
        }

        public void Show()
        {
            this.Hidden = false;
            _itemIconComponent.Hidden = false;
            _button.interactable = true;
        }

        public void Hide()
        {
            this.Hidden = true;
            _itemIconComponent.Hidden = true;
            _button.interactable = false;
        }
    }
}
