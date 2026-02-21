using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{
    public class ShopCategoryTimeComponent : UIObject
    {
        [SerializeField] UIText _text;

        public void Setup(RemainingTimeSpan time)
        {
            _text.SetText(GetText(time));
        }

        string GetText(RemainingTimeSpan nextUpdateTime)
        {
            return TimeSpanFormatter.FormatRemaining(nextUpdateTime);
        }
    }
}
