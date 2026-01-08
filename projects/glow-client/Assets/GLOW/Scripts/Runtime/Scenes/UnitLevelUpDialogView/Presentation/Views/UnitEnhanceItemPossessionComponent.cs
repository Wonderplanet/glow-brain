using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UnityEngine;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitEnhanceItemPossessionComponent : UIObject
    {
        [SerializeField] UIText _possessionNum;
        [SerializeField] UIObject _activeBg;
        [SerializeField] UIObject _disableBg;
        [SerializeField] Color _notEnoughColor;

        public void SetupItem(ItemAmount possession, ItemAmount consume)
        {
            var consumedResult = possession - consume;
            var isMinus = consumedResult.IsMinus();
            SetValue(possession.Value, isMinus);
        }

        public void SetupCoin(Coin possession, Coin consume)
        {
            var consumedResult = possession - consume;
            var isMinus = consumedResult.IsMinus();
            SetValue(possession.HasAmount, isMinus);
        }

        void SetValue(int consumedResult, bool isMinus)
        {
            _possessionNum.SetText(AmountFormatter.FormatAmount(consumedResult));
            _possessionNum.SetColor(isMinus ? _notEnoughColor : Color.black);
            _activeBg.Hidden = isMinus;
            _disableBg.Hidden = !isMinus;
        }
    }
}
