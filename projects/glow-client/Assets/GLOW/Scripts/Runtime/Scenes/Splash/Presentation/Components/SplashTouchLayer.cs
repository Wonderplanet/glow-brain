using System;
using GLOW.Core.Presentation.Components;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Splash.Presentation.Components
{
    public class SplashTouchLayer : UIObject, IPointerDownHandler
    {
        public Action OnTouch { get; set; }

        public void OnPointerDown(PointerEventData eventData)
        {
            OnTouch?.Invoke();
        }
    }
}
