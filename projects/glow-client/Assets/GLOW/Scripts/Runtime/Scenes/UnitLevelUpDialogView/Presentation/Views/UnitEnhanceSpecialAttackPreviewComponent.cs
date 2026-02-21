using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitEnhanceSpecialAttackPreviewComponent : UIObject
    {
        [SerializeField] UIText _specialAttackName;
        [SerializeField] UIText _specialAttackDescription;

        public void Setup(SpecialAttackName specialAttackName, SpecialAttackInfoDescription specialAttackDescription)
        {
            _specialAttackName.SetText(specialAttackName.Value);
            _specialAttackDescription.SetText(specialAttackDescription.Value);
        }
    }
}
