using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitEnhanceSpecialStatusPreviewComponent : UIObject
    {
        [SerializeField] UIText _beforeRush;
        [SerializeField] UIText _afterRush;

        public void Setup(AttackPower before, AttackPower after)
        {
            _beforeRush.SetText("{0}%", before.ToRushPercentageM().ToStringF2());
            _afterRush.SetText("{0}%", after.ToRushPercentageM().ToStringF2());
        }
    }
}
