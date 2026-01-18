using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PassShop.Presentation.View
{
    public class PassShopListView : UIView
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct PassProductInfo
        {
            public ShopPassCellColor PassCellColor;
            public PassShopProductListCell PassCell;
        }
        [SerializeField] PassProductInfo[] _passProductInformations;
        [SerializeField] PassProductInfo _defaultPassProductInformation;
        
        [SerializeField] ScrollRect _scrollRect;
        [SerializeField] Transform _scrollContent;
        [SerializeField] PassShopProductListCell _passShopProductListCell;
        [SerializeField] ChildScaler _childScaler;
        
        public PassShopProductListCell InstantiatePassShopProductListCell(ShopPassCellColor color)
        {
            var info = _passProductInformations.FirstOrDefault(
                info => info.PassCellColor == color,
                _defaultPassProductInformation);
            
            return Instantiate(info.PassCell, _scrollContent);
        }
        
        public void RemoveAllPassShopProductListCells()
        {
            foreach (Transform child in _scrollContent)
            {
                Destroy(child.gameObject);
            }

            _scrollRect.verticalNormalizedPosition = 1f;
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}