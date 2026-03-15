using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShopProductDetail.Presentation.Component;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.View
{
    public class PassShopBuyConfirmView : UIView
    {
        [SerializeField] UIImage _passIconImage;
        [SerializeField] UIText _passNameText;

        [SerializeField] UIObject _passEffectSectionTitleObject;
        [SerializeField] Transform _passEffectCellContainer;
        [SerializeField] PassEffectCellComponent _passEffectCellComponent;

        [SerializeField] UIObject _passRewardSectionTitleObject;
        [SerializeField] Transform _passRewardCellContainer;
        [SerializeField] PassReceivableRewardCellComponent _passReceivableRewardListCellComponent;

        [SerializeField] UIText _passProductPriceText;

        public void SetupPassIcon(PassIconAssetPath passIconAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _passIconImage.Image,
                passIconAssetPath.Value,
                () =>
                {
                    if (!_passIconImage) return;
                    _passIconImage.Image.SetNativeSize();
                });
        }

        public void SetPassName(PassProductName passProductName)
        {
            _passNameText.SetText(passProductName.Value);
        }

        public void SetPassEffectSectionTitleVisible(bool isVisible)
        {
            _passEffectSectionTitleObject.IsVisible = isVisible;
        }
        
        public PassEffectCellComponent InstantiatePassEffectCell()
        {
            return Instantiate(_passEffectCellComponent, _passEffectCellContainer);
        }

        public void SetPassRewardSectionTitleVisible(bool isVisible)
        {
            _passRewardSectionTitleObject.IsVisible = isVisible;
        }
        
        public PassReceivableRewardCellComponent InstantiateProductListCell()
        {
            return Instantiate(_passReceivableRewardListCellComponent, _passRewardCellContainer);
        }

        public void SetPassPrice(RawProductPriceText rawProductPriceText)
        {
            _passProductPriceText.SetText(rawProductPriceText.ToString());
        }
    }
}
