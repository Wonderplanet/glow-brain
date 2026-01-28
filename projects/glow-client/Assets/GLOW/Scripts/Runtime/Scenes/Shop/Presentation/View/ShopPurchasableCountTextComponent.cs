using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopPurchasableCountTextComponent : UIBehaviour, IUIObject
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

        public void Setup(
            PurchasableCount count,
            DisplayCostType costType,
            IsFirstTimeFreeDisplay isFirstTimeFreeDisplay)
        {
            // 初回購入無料時
            if (isFirstTimeFreeDisplay.IsEnable())
            {
                _text.SetText("あと<color=#e82037>1回</color>獲得可能");
                return;
            }

            switch (costType)
            {
                case DisplayCostType.Ad:
                    _text.SetText("あと<color=#e82037>{0}回</color>視聴可能", count.Value);
                    break;
                case DisplayCostType.Free:
                    _text.SetText("あと<color=#e82037>{0}回</color>獲得可能", count.Value);
                    break;
                case DisplayCostType.Coin:
                    _text.SetText("あと<color=#e82037>{0}回</color>交換可能", count.Value);
                    break;
                case DisplayCostType.Diamond:
                case DisplayCostType.PaidDiamond:
                case DisplayCostType.Cash:
                    _text.SetText("あと<color=#e82037>{0}回</color>購入可能", count.Value);
                    break;
            }

            _text.Hidden = false;
        }
    }
}
