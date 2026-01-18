using System;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Core.Presentation.CustomCarousel
{
    public class GlowCustomInfiniteCarouselIndicator : UIComponent
        , IPointerClickHandler
        , IPointerDownHandler
        , IPointerUpHandler
    {
        [SerializeField] UIToggleableImage _toggleableImage;

        Action<GlowCustomInfiniteCarouselIndicator> _onClick;

        public void RegisterClickAction(Action<GlowCustomInfiniteCarouselIndicator> onClickEvent)
        {
            _onClick = onClickEvent;
        }

        public void OnPointerClick(PointerEventData eventData)
        {
            if (_onClick != null) _onClick(this);
        }

        public void OnPointerDown(PointerEventData eventData)
        {
            // do nothing
        }

        public void OnPointerUp(PointerEventData eventData)
        {
            // do nothing
        }

        public virtual bool IsFocus
        {
            set
            {
                _toggleableImage.IsToggleOn = value;
            }
        }
    }
}
