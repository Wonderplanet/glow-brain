using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopProductResourceSpriteComponent : UIObject
    {
        [SerializeField] UIImage _shopProductImage;

        [SerializeField] int[] _resourceAmountRange = { };

        [SerializeField] ShopProductAmountTextComponent _shopProductAmountTextComponent;

        public void SetUp(
            ProductResourceAmount amount,
            ShopProductAssetPath shopProductAssetPath,
            bool isCoin)
        {
            SetUpIcon(amount, shopProductAssetPath, isCoin);
            _shopProductAmountTextComponent.SetUp(amount);
        }

        void SetUpIcon(
            ProductResourceAmount amount,
            ShopProductAssetPath shopProductAssetPath,
            bool isCoin)
        {
            if (!shopProductAssetPath.IsEmpty())
            {
                _shopProductImage.IsVisible = true;

                SpriteLoaderUtil.Clear(_shopProductImage.Image);
                UISpriteUtil.LoadSpriteWithFade(_shopProductImage.Image, shopProductAssetPath.Value);
            }
            else
            {
                _shopProductImage.IsVisible = true;
                var index = GetIndex(amount);
                var assetPath = isCoin
                    ? ShopProductAssetPath.CreateCoin(index + 1).Value
                    : ShopProductAssetPath.CreateDiamond(index + 1).Value;

                UISpriteUtil.LoadSpriteWithFade(_shopProductImage.Image, assetPath);
            }
        }

        int GetIndex(ProductResourceAmount amount)
        {
            for(var i = 0; i < _resourceAmountRange.Length; i++)
            {
                if (amount.Value <= _resourceAmountRange[i])
                {
                    return i;
                }
            }

            return _resourceAmountRange.Length - 1;
        }
    }
}
