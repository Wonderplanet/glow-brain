using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopProductAmountTextComponent : UIBehaviour, IUIObject
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
        public void SetUp(ProductResourceAmount amount)
        {
            _text.SetText(amount.ToStringWithMultiplicationAndSeparate());
            _text.Hidden = false;
        }
    }
}
