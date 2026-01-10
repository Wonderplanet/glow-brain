using GLOW.Core.Presentation.Components;
using TMPro;
using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceAbilityDescriptionComponent : UIObject
    {
        [SerializeField] UIText _text;

        public void SetText(string text)
        {
            var alignment = text.Length < 10 ? TextAlignmentOptions.Center : TextAlignmentOptions.Left;
            _text.SetAlignment(alignment);
            _text.SetText(text);
        }
    }
}
