using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public interface IUIObject
    {
        RectTransform RectTransform { get; }

        bool IsVisible { get; set; }
        bool Hidden { get; set; }
    }
}
