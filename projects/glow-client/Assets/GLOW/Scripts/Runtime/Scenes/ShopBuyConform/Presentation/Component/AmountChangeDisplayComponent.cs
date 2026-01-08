using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Component
{
    public class AmountChangeDisplayComponent : UIObject
    {
        [SerializeField] UIImage _icon;
        [SerializeField] UIText _beforeResourceAmount;
        [SerializeField] UIText _afterResourceAmount;

        public void Setup(ItemIconAssetPath iconAssetPath, ItemAmount beforeAmount, ItemAmount afterAmount)
        {
            UISpriteUtil.LoadSpriteWithFade(_icon.Image, iconAssetPath.Value);

            _beforeResourceAmount.SetText(beforeAmount.ToStringSeparated());
            _afterResourceAmount.SetText(afterAmount.ToStringSeparated());
        }
    }
}
