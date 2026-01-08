using UnityEngine.Events;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace WPFramework.Presentation.Views
{
    public class InfiniteCarouselCellButton : Button
    {
        public UnityEvent onPointerDown = new UnityEvent();

        public override void OnPointerDown(PointerEventData eventData)
        {
            // NOTE: タップ時(押す時)に反応
            base.OnPointerDown(eventData);
            onPointerDown.Invoke();
        }
    }
}
