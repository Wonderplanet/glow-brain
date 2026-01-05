using System;
using GLOW.Core.Presentation.Components;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Title.Presentations.Components
{
    public class TitleTouchLayer : UIObject, IPointerDownHandler
    {
        public Action OnTouch { get; set; }
        public void OnPointerDown(PointerEventData eventData)
        {
            OnTouch?.Invoke();
        }
    }
}
