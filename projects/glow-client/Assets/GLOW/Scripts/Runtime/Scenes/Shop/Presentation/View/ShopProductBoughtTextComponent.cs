using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopProductBoughtTextComponent : UIBehaviour, IUIObject
    {
        [SerializeField] UIText _text;

        RectTransform IUIObject.RectTransform => _text.RectTransform;

        bool IUIObject.IsVisible
        {
            get => _text.IsVisible;
            set => _text.IsVisible = value;
        }

        bool IUIObject.Hidden
        {
            get => _text.Hidden;
            set => _text.Hidden = value;
        }

        public void Setup(DisplayCostType costType)
        {
            switch (costType)
            {
                case DisplayCostType.Ad:
                case DisplayCostType.Free:
                    _text.SetText("獲得済み");
                    break;
                case DisplayCostType.Coin:
                    _text.SetText("交換済み");
                    break;
                case DisplayCostType.Diamond:
                case DisplayCostType.PaidDiamond:
                case DisplayCostType.Cash:
                    _text.SetText("購入済み");
                    break;
                default:
                    _text.SetText("");
                    break;
            }
            _text.Hidden = false;
        }
    }
}
