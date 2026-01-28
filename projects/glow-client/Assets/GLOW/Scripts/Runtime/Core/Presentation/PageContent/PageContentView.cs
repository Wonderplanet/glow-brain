using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.PageContent
{
    public interface IPageContentViewDelegate
    {
        void OnScroll(float normailzedPosition);
    }

    /// <summary>
    /// UIPageViewからドラッグ操作を除いたもの
    /// </summary>
    public class PageContentView : UIView
    {
        const float TransitionSeconds = 0.25f;

        [SerializeField] PageContentControl _pageControl;
        [SerializeField] RectTransform _viewport;
        [SerializeField] RectTransform _content;
        [SerializeField] GameObject _pointerEventTrigger; // ButtonがあるContentsでもPointerDown, Upをハンドリングするためのもの
        [SerializeField] AnimationCurve _easing = new AnimationCurve();

        Dictionary<UIView, int> _pageContents = new Dictionary<UIView, int>();

        float _startTime;
        float _startPagePosition;

        float _pagePosition;
        float _maxPagePosition;
        float _minPagePosition;
        int _targetPage;
        int _pointerId;

        public PageContentControl PageControl => _pageControl;
        public IPageContentViewDelegate Delegate { get; set; }

        public float PagePosition
        {
            get => _pagePosition;
            set
            {
                _pagePosition = value;
                _content.anchoredPosition = new Vector2(-value * _content.rect.width, 0);
                Delegate?.OnScroll(PagePosition);
            }
        }

        protected override void OnEnable()
        {
            if ((int)PagePosition != _targetPage)
            {
                Animate(_targetPage);
            }
        }

        Vector2 GetLocalPosition(Vector2 screenPosition, Camera uiCamera)
        {
            RectTransformUtility.ScreenPointToLocalPointInRectangle((RectTransform)transform, screenPosition, uiCamera, out var result);
            return result;
        }

        public void Animate(int targetPage)
        {
            _startPagePosition = PagePosition;
            this._targetPage = targetPage;
            _startTime = Time.time;
            _pointerEventTrigger.SetActive(true);
        }

        void Update()
        {
            Animation();
        }

        void Animation()
        {
            float time = (Time.time - _startTime) / TransitionSeconds;
            float evaluate = _easing.Evaluate(time);

            evaluate = Mathf.Clamp01(evaluate);

            if ((int)evaluate == 1)
            {
                PagePosition = _targetPage;
                _pointerEventTrigger.SetActive(false);
            }
            else
            {
                PagePosition = Mathf.Lerp(_startPagePosition, _targetPage, evaluate);
            }
        }

        void UpdateBounds()
        {
            if (_pageContents.Count == 0)
            {
                _minPagePosition = 0;
                _maxPagePosition = 0;
                _targetPage = 0;
                return;
            }

            _minPagePosition = _pageContents.Values.Min();
            _maxPagePosition = _pageContents.Values.Max();
            _targetPage = (int)Mathf.Clamp(_targetPage, _minPagePosition, _maxPagePosition);
        }

        public void LayoutView(int page, UIView view)
        {
            var rectTransform = (RectTransform)view.transform;
            rectTransform.SetParent(_content, false);
            rectTransform.anchoredPosition = new Vector2(page * _viewport.rect.width, 0);
            _pageContents.Remove(view);
            _pageContents.Add(view, page);
            UpdateBounds();
        }

        public void RemoveView(UIView view)
        {
            _pageContents.Remove(view);
            UpdateBounds();
        }
    }
}
