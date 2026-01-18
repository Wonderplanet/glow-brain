using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopProductInfo.Presentation.View
{
    public class ShopProductInfoView : UIView
    {
        [SerializeField] ShopProductInfoPlateComponent _infoPlateComponentTop;

        [SerializeField] ShopProductInfoPlateComponent _infoPlateComponentBottom;

        public void SetupTopPlate(PlayerResourceIconViewModel model, ProductName productName)
        {
            _infoPlateComponentTop.Setup(model, productName);
        }

        public void SetupBottomPlate(PlayerResourceIconViewModel model, ProductName productName)
        {
            _infoPlateComponentBottom.gameObject.SetActive(true);
            _infoPlateComponentBottom.Setup(model, productName);
        }

        public ShopProductInfoPlateComponent InstantiateInfoPlateComponent()
        {
            return Instantiate(_infoPlateComponentBottom);
        }
    }
}
