using System;
using System.Collections.Generic;
using UIKit;
using UnityEngine;
using UnityEngine.Events;
using UnityEngine.UI;
using WPFramework.Presentation.Views;

namespace GLOW.Core.Presentation.CustomCarousel
{
    [RequireComponent(typeof(InfiniteCarouselCellButton))]
    public class GlowCustomInfiniteCarouselCell : InfiniteCarouselCell
    {
        public class AccessoryButtonEvent : UnityEvent<object> { }

        List<Button> _accessoryButtons = new List<Button>();
        AccessoryButtonEvent _accessoryButtonEvent = new AccessoryButtonEvent();
        Action<bool> _onChangeDraggingStatus;
        Action<int> _onChangeCenterIndex;

        public AccessoryButtonEvent AccessoryButtonTapEvent => _accessoryButtonEvent;
        public Action<bool> OnChangeDraggingStatus => _onChangeDraggingStatus;
        public Action<int> OnChangeCenterIndex => _onChangeCenterIndex;
        protected void AddButton(Button button, object identifier)
        {
            _accessoryButtons.Add(button);
            button.onClick.AddListenerAsExclusive(() => _accessoryButtonEvent.Invoke(identifier));
        }

        internal void SetOnChangeDraggingStatus(Action<bool> onChangeDraggingStatus)
        {
            _onChangeDraggingStatus = onChangeDraggingStatus;
        }
        internal void SetOnChangeCenterIndex(Action<int> onChangeCenterIndex)
        {
            _onChangeCenterIndex = onChangeCenterIndex;
        }
        protected override void OnDestroy()
        {
            base.OnDestroy();
            foreach (var button in _accessoryButtons)
            {
                button.onClick.RemoveAllListeners();
            }
        }
    }
}
