using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Views
{
    [RequireComponent(typeof(InfiniteCarouselCellButton))]
    public class InfiniteCarouselCell : UIComponent
    {
        public int Index { get; set; }

        public RectTransform RectTransform => (RectTransform)transform;

        IInfiniteCarouselCellDelegate _tapDelegate;

        protected override void Awake()
        {
            base.Awake();

            var button = GetComponent<InfiniteCarouselCellButton>();
            // NOTE: ボタン上でタップして離した時に検知する
            button.onClick.AddListenerAsExclusive(OnTap);
            // NOTE: InfiniteCarouselCellButtonのOnPointerDownを検知する(押す時)
            button.onPointerDown.AddListener(OnPointerDown);
        }

        public void RegisterDelegate(IInfiniteCarouselCellDelegate tapDelegate)
        {
            _tapDelegate = tapDelegate;
        }

        public void UnregisterDelegate(IInfiniteCarouselCellDelegate tapDelegate)
        {
            if (_tapDelegate == tapDelegate)
            {
                _tapDelegate = null;
            }
        }

        void OnTap()
        {
            _tapDelegate?.OnTap(Index);
        }

        void OnPointerDown()
        {
            _tapDelegate?.OnPointerDown(Index);
        }
    }
}
