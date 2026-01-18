using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.ShopBuyConform.Presentation.Component
{
    public class UseResourceAmountChangeDisplayComponent : UIObject
    {
        [SerializeField] UIText _currentResourceAmount;

        [SerializeField] UIText _afterResourceAmount;

        public void SetupPaidDiamondAmount(PaidDiamond current, PaidDiamond after)
        {
            _currentResourceAmount.SetText(current.ToStringSeparated());
            _afterResourceAmount.SetText(after.ToStringSeparated());
        }

        public void SetupFreeDiamondAmount(FreeDiamond current, FreeDiamond after)
        {
            _currentResourceAmount.SetText(current.ToStringSeparated());
            _afterResourceAmount.SetText(after.ToStringSeparated());
        }

        public void SetupCoinAmount(Coin current, Coin after)
        {
            _currentResourceAmount.SetText(current.ToStringSeparated());
            _afterResourceAmount.SetText(after.ToStringSeparated());
        }
    }
}
