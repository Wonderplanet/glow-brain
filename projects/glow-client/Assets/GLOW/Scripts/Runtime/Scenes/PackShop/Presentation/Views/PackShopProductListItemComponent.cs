using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopProductListItemComponent : UICollectionViewCell
    {
        [SerializeField] PlayerResourceIconComponent _icon;
        [SerializeField] Button _button;

        public Button Button => _button;

        public void Setup(PlayerResourceIconViewModel viewModel)
        {
            _icon.Setup(viewModel);
        }
    }
}
