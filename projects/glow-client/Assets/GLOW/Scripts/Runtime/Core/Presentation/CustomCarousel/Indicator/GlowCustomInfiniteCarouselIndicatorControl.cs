using System;
using System.Collections.Generic;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.CustomCarousel
{
    public class GlowCustomInfiniteCarouselIndicatorControl : UIComponent
    {
        [SerializeField] protected GlowCustomInfiniteCarouselIndicator _indicagtorTemplate;
        [SerializeField] protected Transform _indicatorLayout;

        public Action<int> OnClickEvent = (_) => { };

        protected List<GlowCustomInfiniteCarouselIndicator> Indicators = new List<GlowCustomInfiniteCarouselIndicator>();
        int _numberOfPages;
        int _currentPage;

        protected override void Awake()
        {
            base.Awake();
            _indicagtorTemplate.Hidden = true;
        }

        public int CurrentPage
        {
            get
            {
                return _currentPage;
            }
            set
            {
                if (_numberOfPages <= 0 || Indicators.Count == 0) return;

                foreach (var i in Indicators) i.IsFocus = false;
                _currentPage = ((value % _numberOfPages) + _numberOfPages) % _numberOfPages;
                var indicator = Indicators[_currentPage];
                indicator.IsFocus = true;
            }
        }

        public int NumberOfPages
        {
            get
            {
                return _numberOfPages;
            }
            set
            {
                _numberOfPages = value;
                RebuildIndicators(_numberOfPages);
                CurrentPage = _currentPage;
            }
        }

        void RebuildIndicators(int number)
        {
            for (int i = Indicators.Count - 1; i >= number; i--)
            {
                var indicator = Indicators[i];
                indicator.RegisterClickAction(null);
                Indicators.RemoveAt(i);
                Destroy(indicator.gameObject);
            }
            for (int i = Indicators.Count; i < number; i++)
            {
                var indicator = Instantiate(_indicagtorTemplate, _indicatorLayout, false);
                indicator.Hidden = false;
                Indicators.Add(indicator);
                indicator.RegisterClickAction(OnClick);
            }
        }

        void OnClick(GlowCustomInfiniteCarouselIndicator indicator)
        {
            OnClickEvent(Indicators.IndexOf(indicator));
        }

        // public bool HidesForSinglePage { get; set; }
    }
}
