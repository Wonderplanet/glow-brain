using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCategoryNameTextComponent : UIBehaviour, IUIObject
    {
        [SerializeField] UIText _text;

        [SerializeField] string[] _categoryNames = {};

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

        public void Setup(DisplayShopProductType type)
        {
            _text.SetText(GetName(type));

            _text.Hidden = false;
        }

        string GetName(DisplayShopProductType type)
        {
            var index = (int)type;

            return index >= _categoryNames.Length ? "" : _categoryNames[index];
        }
    }
}
