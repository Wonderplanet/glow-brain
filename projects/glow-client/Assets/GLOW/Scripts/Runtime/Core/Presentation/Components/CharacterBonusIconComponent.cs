using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class CharacterBonusIconComponent : UIObject
    {
        [SerializeField] UIText _bonusText;

        public void Setup(EventBonusPercentage bonus)
        {
            _bonusText.SetText(ZString.Format("<size=14>+</size><size=15>{0}</size><size=12>%</size>", bonus.Value));
        }
    }
}
