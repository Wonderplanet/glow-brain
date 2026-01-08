using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Shop.Presentation.View
{

    public class ShopProductDisplayTermComponent : UIObject
    {
        [SerializeField] UIText _longTermText;

        public void Setup(RemainingTimeSpan term)
        {
            if(term.IsEmpty()) return;
            
            _longTermText.SetText(TimeSpanFormatter.FormatRemaining(term));
        }
    }
}
