using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopStorePriceTextComponent : UIObject
    {
        [SerializeField] UIText _text;

        public void Setup(RawProductPriceText price)
        {
            _text.SetText(price.ToString());
            _text.Hidden = false;
        }
    }
}
