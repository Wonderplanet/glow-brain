using System;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(ScrollRect))]
    public class ScrollRectBarViewControl : MonoBehaviour,
        IBeginDragHandler,
        IEndDragHandler
    {
        [SerializeField] bool _hideScrollbarWhenNotAnimating = true;
        ScrollRectbarViewController _controller;
        ScrollRect _scrollRect;
        bool _dragging;

        public void Start()
        {
            _scrollRect = GetComponent<ScrollRect>();
            if (_scrollRect.verticalScrollbar != null)
            {
                _controller = new ScrollRectbarViewController(_scrollRect.verticalScrollbar, !_hideScrollbarWhenNotAnimating);
            }
            else if (_scrollRect.horizontalScrollbar != null)
            {
                _controller = new ScrollRectbarViewController(_scrollRect.horizontalScrollbar, !_hideScrollbarWhenNotAnimating);
            }
        }
        void LateUpdate()
        {
            if (_controller == null) return;

            if (_hideScrollbarWhenNotAnimating &&
                !_dragging &&
                _controller.Shown &&
                !_controller.HideTrigger &&
                _scrollRect.velocity.sqrMagnitude < 0.05f)
            {
                _controller.TriggerHide();
            }
            _controller.UpdateControl();
        }

        void IBeginDragHandler.OnBeginDrag(PointerEventData eventData)
        {
            _dragging = true;
            if (_controller != null) _controller.Show();
        }
        void IEndDragHandler.OnEndDrag(PointerEventData eventData)
        {
            _dragging = false;
        }

        void OnDisable()
        {
            _dragging = false;
        }


    }

    //UIKit.UICollectionView.ScrollbarAnimationControlと同一実装
    public class ScrollRectbarViewController
    {
        const float DelayHide = 1.0f;

        CanvasGroup _canvasGroup;
        bool _shown;
        float _velocity;
        bool _triggerHide;
        float _triggerTime;
        float _cachedAlpha;
        float _targetAlpha;

        public ScrollRectbarViewController(Scrollbar scrollbar, bool shown)
        {
            _canvasGroup = scrollbar.GetComponent<CanvasGroup>();
            if (_canvasGroup == null) _canvasGroup = scrollbar.gameObject.AddComponent<CanvasGroup>();
            Shown = shown;
            _cachedAlpha = _targetAlpha;
            _canvasGroup.alpha = _targetAlpha;
        }

        public bool Shown
        {
            get { return _shown; }
            private set
            {
                _targetAlpha = value ? 1.0f : 0.0f;
                _shown = value;
            }
        }

        public bool HideTrigger { get { return _triggerHide; } }

        public void Show()
        {
            Shown = true;
            _triggerHide = false;
        }

        public void TriggerHide()
        {
            _triggerHide = true;
            _triggerTime = 0;
        }

        public void UpdateControl()
        {
            const double tolerance = 0.0001;

            if (_triggerHide)
            {
                _triggerTime += Time.deltaTime;
                if (_triggerTime >= DelayHide) Shown = false;
            }
            if (Math.Abs(_cachedAlpha - _targetAlpha) > tolerance)
            {
                var alpha = Mathf.SmoothDamp(_cachedAlpha, _targetAlpha, ref _velocity, 0.1f);
                _canvasGroup.alpha = alpha;
                _cachedAlpha = alpha;
            }
        }
    }
}
