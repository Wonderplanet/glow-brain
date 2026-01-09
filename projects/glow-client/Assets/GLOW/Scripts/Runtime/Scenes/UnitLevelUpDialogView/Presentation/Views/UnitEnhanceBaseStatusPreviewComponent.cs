using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitEnhanceBaseStatusPreviewComponent : UIObject
    {
        [SerializeField] UIText _beforeHp;
        [SerializeField] UIText _afterHp;
        [SerializeField] UIText _beforeAttackPower;
        [SerializeField] UIText _afterAttackPower;

        public void SetupHP(HP before, HP after)
        {
            _beforeHp.SetText(before.ToString());
            _afterHp.SetText(after.ToString());
        }

        public void SetupAttackPower(AttackPower before, AttackPower after)
        {
            _beforeAttackPower.SetText(before.ToStringN0());
            _afterAttackPower.SetText(after.ToStringN0());
        }
    }
}
