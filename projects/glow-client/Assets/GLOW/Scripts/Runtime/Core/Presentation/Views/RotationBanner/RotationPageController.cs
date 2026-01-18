using System.Collections;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Extensions;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Views.RotationBanner
{
    public abstract class RotationPageController<VC, VM> : IUIPageViewControllerDataSource
        , IUIPageViewControllerDelegate
        , IUIPageViewControllerPageControlDataSource
        where VC : UIViewController
        where VM : IRotationPageItemViewModel
    {
        protected IReadOnlyList<VM> _itemViewModels = new List<VM>();
        protected VC[] _itemViewControllers;
        protected UIViewController _parentViewController;
        UIPageViewController _pageViewController;
        UIPageView _pageView;
        int _originalPageCount;
        int _actualIndex;
        float _scrollInterval = 3f;

        protected abstract VC[] NewItemViewControllersArray(int count);
        protected abstract void SetItemViewControllerIfNeed(int index);
        protected abstract void PlayIndicatorClickSound();
        protected abstract void OnSwipeAnimationFinished();
        protected abstract void OnNonSwipeAnimationFinished();

        // ページ数2のとき、見せかけのIndexを表すことに注意
        protected int PageViewPresentationIndex => _pageView.PageControl.CurrentPage;

        protected int ActualIndex
        {
            get => _actualIndex;
            set
            {
                _actualIndex = value;
                _pageView.PageControl.CurrentPage = value % _originalPageCount;
            }
        }

        protected VC CurrentViewController => _itemViewControllers[ActualIndex];

        /*
            ページ数2で素直に循環させるとUIPageViewControllerの実装上の都合で、エラーが起きるので
            その回避処理が入っていることに注意
        */
        public void SetUpPages(IReadOnlyList<VM> items, UIViewController parentViewController, UIPageView pageView)
        {
            if (_itemViewControllers != null)
            {
                RemoveItemViewControllers();
            }

            _parentViewController = parentViewController;
            _pageView = pageView;
            _originalPageCount = items.Count;

            // 順番依存1
            if (_pageView.PageControl.CurrentPage != 0)
            {
                _pageView.PageControl.CurrentPage = 0;
            }

            if (_originalPageCount == 2)
            {
                //ちょっと苦しい
                var tmp = items.Cast<IRotationPageItemViewModel>().ToList();
                foreach (var i in items)
                {
                    tmp.Add(i.Duplicate());
                }
                _itemViewModels = tmp.Cast<VM>().ToList();
            }
            else
            {
                _itemViewModels = items;
            }

            if (_pageViewController == null || !_parentViewController.ChildViewControllers.Contains(_pageViewController))
            {
                _pageViewController = new UIPageViewController
                {
                    View = _pageView,
                    DataSource = this,
                    Delegate = this,
                    PageControlDataSource = this,
                };

                parentViewController.AddChild(_pageViewController);
                _pageViewController.ViewDidLoad();

                _pageView.PageControl.OnClickEvent += OnIndicatorClick;
            }

            _itemViewControllers = NewItemViewControllersArray(_itemViewModels.Count);
            _pageView.PageControl.NumberOfPages = _originalPageCount;
            _pageViewController.SetViewControllers(
                // 順番依存2(PageViewPresentationIndex)
                new List<UIViewController> { GetViewController(PageViewPresentationIndex) },
                UIPageViewController.NavigationDirection.Forward,
                true
            );

            // 順番依存3
            _pageView.PagePosition = 0;

            if (_originalPageCount > 1)
            {
                _pageView.StopAllCoroutines();
                _pageView.StartCoroutine(AutoScrollCoroutine());
            }

            _pageView.CanDrag = _originalPageCount > 1;
        }


        public void RemoveItemViewControllers()
        {
            foreach (var item in _itemViewControllers)
            {
                item?.Dismiss();
            }
        }

        public bool IsSameViewModels(IReadOnlyList<VM> items)
        {
            return _itemViewModels.SequenceEqual(items);
        }

        public void Restart()
        {
            _pageView?.StartCoroutine(AutoScrollCoroutine());
        }

        public void SetAutoScrollInterval(float scrollInterval)
        {
            _scrollInterval = scrollInterval;
        }

        protected UIViewController GetViewController(int index)
        {
            if (index < 0) return null;
            if (index > _itemViewControllers.Length - 1) return null;
            SetItemViewControllerIfNeed(index);
            return _itemViewControllers[index];
        }

        /*
            MoveRight, MoveLeftの実装により
            ページが循環するように、現在のページの前後のページを取得する
        */
        protected void MoveRight()
        {
            var vcIndex = RightVcIndex(PageViewPresentationIndex);
            var vc = GetViewController(vcIndex);
            if (vc == null) return;
            MoveToViewController(vc, UIPageViewController.NavigationDirection.Forward);
        }

        protected void MoveLeft()
        {
            var vcIndex = LeftVcIndex(PageViewPresentationIndex);
            var vc = GetViewController(vcIndex);
            if (vc == null) return;
            MoveToViewController(vc, UIPageViewController.NavigationDirection.Reverse);
        }

        protected void MoveToViewController(UIViewController controller, UIPageViewController.NavigationDirection navigation)
        {
            var index = _itemViewControllers.IndexOf(controller);
            _pageViewController.SetViewControllers(
                new List<UIViewController> { controller },
                navigation,
                true,
                (_) =>
                {
                    ActualIndex = index;
                    OnNonSwipeAnimationFinished();
                }
            );
        }

        void OnIndicatorClick(int index)
        {
            if (index == PageViewPresentationIndex) return;
            PlayIndicatorClickSound();
            var direction = index < PageViewPresentationIndex ? UIPageViewController.NavigationDirection.Reverse : UIPageViewController.NavigationDirection.Forward;
            MoveToViewController(GetViewController(index), direction);

            _pageView.StopAllCoroutines();
            _pageView.StartCoroutine(AutoScrollCoroutine());
        }

        IEnumerator AutoScrollCoroutine()
        {
            if (_scrollInterval <= 0f)
            {
                yield break;
            }

            while (true)
            {
                yield return new WaitForSeconds(_scrollInterval);
                MoveRight();
            }
        }

        /*
            ViewControllerBefore, ViewControllerAfterの実装により
            ページが循環するように、現在のページの前後のページを取得する
        */
        UIViewController IUIPageViewControllerDataSource.ViewControllerBefore(UIPageViewController pageViewController, UIViewController viewController)
        {
            if (_originalPageCount == 1) return null; // nullを返すことでUIPageViewControllerが何もしないでくれるようになる
            var current = _itemViewControllers.IndexOf(viewController);
            var before = LeftVcIndex(current);
            return GetViewController(before);
        }

        UIViewController IUIPageViewControllerDataSource.ViewControllerAfter(UIPageViewController pageViewController, UIViewController viewController)
        {
            if (_originalPageCount == 1) return null; // nullを返すことでUIPageViewControllerが何もしないでくれる様になる
            var current = _itemViewControllers.IndexOf(viewController);
            var after = RightVcIndex(current);

            return GetViewController(after);
        }

        void IUIPageViewControllerDelegate.WillTransitionTo(UIPageViewController pageViewController, UIViewController[] pendingViewControllers)
        {
            if (_originalPageCount == 1) return;
            _pageView.StopAllCoroutines();
        }

        void IUIPageViewControllerDelegate.DidFinishAnimating(UIPageViewController pageViewController, bool finished, UIViewController[] previousViewControllers, bool transitionCompleted)
        {
            if (_originalPageCount == 1) return;
            if (finished)
            {
                ActualIndex = _itemViewControllers.IndexOf(pageViewController.ViewControllers.First());
            }
            if (transitionCompleted) OnSwipeAnimationFinished();
            _pageView.StartCoroutine(AutoScrollCoroutine());
        }

        int IUIPageViewControllerPageControlDataSource.PresentationCount(UIPageViewController pageViewController)
        {
            return _pageView.PageControl.NumberOfPages;
        }

        int IUIPageViewControllerPageControlDataSource.PresentationIndex(UIPageViewController pageViewController)
        {
            return PageViewPresentationIndex;
        }

        int RightVcIndex(int current)
        {
            /* ページ数２のときの挙動を考慮して、PageControl.NumberOfPageではなくviewControllers.Lengthを使うことに注意 */
            return current >= _itemViewControllers.Length - 1 ? 0 : current + 1;
        }

        int LeftVcIndex(int current)
        {
            /* ページ数２のときの挙動を考慮して、PageControl.NumberOfPageではなくviewControllers.Lengthを使うことに注意 */
            return current <= 0 ? _itemViewControllers.Length - 1 : current - 1;
        }
    }
}
