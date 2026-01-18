using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    public class EncyclopediaRewardEffectTypeComponent : UIObject
    {
        [SerializeField] UIText _effectValue;

        public void Setup(UnitEncyclopediaEffectValue effectValue)
        {
            _effectValue.SetText(effectValue.ToString());
        }
    }
}
