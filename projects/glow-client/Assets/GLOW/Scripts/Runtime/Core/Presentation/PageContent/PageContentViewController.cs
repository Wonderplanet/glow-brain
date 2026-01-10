using System;
using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.PageContent
{
    /// <summary>
    /// PageContentViewControllerから必要な機能以外をオミットしたもの
    /// </summary>
    public class PageContentViewController : UIViewController<PageContentView>, IPageContentViewDelegate
    {
        public enum NavigationDirection
        {
            Forward = 1,
            Reverse = -1
        }

        UIViewController _viewController;
        UIViewController _pendingViewController;
        List<UIViewController> _handlingViewControllers = new List<UIViewController>();

        public IPageContentViewControllerDataSource DataSource { get; set; }
        public IPageContentViewControllerPageControlDataSource PageControlDataSource { get; set; }

        public IReadOnlyCollection<UIViewController> ViewControllers => new List<UIViewController>() { _viewController }; // TEMP

        Dictionary<UIViewController, Action<bool>> _initializingTask = new Dictionary<UIViewController, Action<bool>>();
        Dictionary<UIViewController, int> _pageContents = new Dictionary<UIViewController, int>();

        NavigationDirection _transitionDirection = NavigationDirection.Forward;

        bool IsInitializing => _initializingTask.Count != 0;

        protected override bool ShouldAutomaticallyForwardAppearanceMethods => false;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.Delegate = this;
        }

        public void ResetViews()
        {
            var removeViews = _pageContents.Keys.ToArray();
            foreach (var viewController in removeViews)
            {
                RemoveChildViewController(viewController);
                viewController.Dismiss();
            }
            _viewController = null;
            _pendingViewController = null;
            _handlingViewControllers.Clear();
            _pageContents.Clear();
        }

        void AddChildViewController(int page, UIViewController controller)
        {
            if (!ChildViewControllers.Contains(controller)) AddChild(controller);
            _pageContents.Remove(controller);
            _pageContents.Add(controller, page);
            ActualView.LayoutView(page, controller.View);
        }

        void RemoveChildViewController(UIViewController controller)
        {
            RemoveChild(controller);
            _pageContents.Remove(controller);
            ActualView.RemoveView(controller.View);
        }

        public void SetViewControllers(
            List<UIViewController> controllers,
            NavigationDirection direction,
            bool animated,
            Action<bool> completion = null)
        {
            // note: Multi Page Control is not implemetented.
            if (controllers.Count >= 2) throw new ArgumentException("doesn't match the number required");
            var initViewController = controllers[0];
            if (initViewController == null) throw new ArgumentException("init view controller is null");

            if (IsInitializing && _initializingTask.ContainsKey(initViewController))
            {
                Debug.LogWarning("View Controller is initializing");
                completion?.Invoke(false);
                return;
            }

            _handlingViewControllers.RemoveAll(c => c == null);
            foreach (var c in _handlingViewControllers.ToArray())
            {
                if (c == _viewController || c == _pendingViewController) continue;

                if (_initializingTask.ContainsKey(c))
                {
                    _initializingTask[c]?.Invoke(false);
                    _initializingTask.Remove(c);
                }

                _handlingViewControllers.Remove(c);
                RemoveChildViewController(c);
                c.View.Hidden = true;
            }

            if (_viewController == null)
            {
                // First Initialize
                initViewController.View.Hidden = false;
                AddChildViewController(0, initViewController);
                _handlingViewControllers.Add(initViewController);
                _viewController = initViewController;

                initViewController.BeginAppearanceTransition(true, false);
                initViewController.EndAppearanceTransition();
                completion?.Invoke(true);
            }
            else if (_viewController == initViewController)
            {
                if (_pendingViewController == null)
                {
                    initViewController.BeginAppearanceTransition(false, false);
                    initViewController.EndAppearanceTransition();
                    initViewController.BeginAppearanceTransition(true, false);
                    initViewController.EndAppearanceTransition();
                }

                completion?.Invoke(_pendingViewController == null);
            }
            else if (_pendingViewController == initViewController)
            {
                // In Transition
                _initializingTask.Add(initViewController, completion);
            }
            else
            {
                int page;

                if (IsInitializing && direction != _transitionDirection) CancelInitializing();

                if (direction == NavigationDirection.Forward)
                {
                    var last = _handlingViewControllers.Last();
                    _handlingViewControllers.Add(initViewController);
                    page = _pageContents[last] + 1;
                    AddChildViewController(page, initViewController);
                }
                else
                {
                    var first = _handlingViewControllers.First();
                    _handlingViewControllers.Insert(0, initViewController);
                    page = _pageContents[first] - 1;
                    AddChildViewController(page, initViewController);
                }

                _initializingTask.Add(initViewController, completion);

                if (animated)
                {

                    ActualView.Animate(page);
                }
                else
                {
                    ActualView.PagePosition = page;
                }
            }

            ActualView.UserInteraction = !IsInitializing;

            if (PageControlDataSource == null)
            {
                ActualView.PageControl.Hidden = true;
            }
            else
            {
                ActualView.PageControl.Hidden = false;
                ActualView.PageControl.NumberOfPages = PageControlDataSource.PresentationCount(this);
                ActualView.PageControl.CurrentPage = PageControlDataSource.PresentationIndex(this);
                ActualView.PageControl.OnClickEvent = OnPageControlEvent;
            }

            LoadViewControllersIfNeeds();

        }

        void OnPageControlEvent(int page)
        {
            if (_pendingViewController != null) return;
            if (IsInitializing) return;

            int current = ActualView.PageControl.CurrentPage;
            if (current == page) return;

            int direction = (int)Mathf.Sign(page - current);
            int targetPage = _pageContents[_viewController] + direction;
            ActualView.Animate(targetPage);
        }

        void CancelInitializing()
        {
            foreach (var task in _initializingTask)
            {
                task.Value?.Invoke(false);
            }
            _initializingTask.Clear();
        }

        public void OnScroll(float page)
        {
            if (!_pageContents.ContainsKey(_viewController))
            {
                Debug.LogWarning("page not found for the view controller");
                return;
            }
            float diff = page - _pageContents[_viewController];

            var direction = diff < 0 ? NavigationDirection.Reverse : NavigationDirection.Forward;
            if (diff != 0 && (_pendingViewController == null || _transitionDirection != direction))
            {
                LoadViewControllersIfNeeds();

                int index = _handlingViewControllers.IndexOf(_viewController);
                var willTransitionIndex = index + (int)direction;
                if (willTransitionIndex < 0 || willTransitionIndex >= _handlingViewControllers.Count) return; // 暫定対応
                var willTransitionViewController = _handlingViewControllers[willTransitionIndex];
                _transitionDirection = direction;

                if (willTransitionViewController != null && _pendingViewController != willTransitionViewController)
                {
                    WillTransitionViewController(willTransitionViewController);
                }
            }

            if (_pendingViewController != null)
            {
                if (Mathf.Abs(diff) >= 1f)
                {
                    OnComplete();
                }
                else if (diff == 0)
                {
                    OnCancel();
                }
            }
        }

        void WillTransitionViewController(UIViewController willTransitionViewController)
        {
            willTransitionViewController.View.Hidden = false;
            willTransitionViewController.BeginAppearanceTransition(true, true);

            if (_pendingViewController != null)
            {
                _pendingViewController.BeginAppearanceTransition(false, true);
                _pendingViewController.EndAppearanceTransition();
                _pendingViewController.View.Hidden = true;
            }
            else
            {
                _viewController.BeginAppearanceTransition(false, true);
            }

            _pendingViewController = willTransitionViewController;
        }

        void OnComplete()
        {
            _pendingViewController.EndAppearanceTransition();
            _viewController.EndAppearanceTransition();

            UIViewController removeViewController;
            if (_transitionDirection == NavigationDirection.Forward)
            {
                removeViewController = _handlingViewControllers.First();
                _handlingViewControllers.RemoveAt(0);
            }
            else
            {
                removeViewController = _handlingViewControllers.Last();
                _handlingViewControllers.RemoveAt(_handlingViewControllers.Count() - 1);
            }
            if (removeViewController != null && 2 < _pageContents.Count) RemoveChildViewController(removeViewController);

            var previouseViewController = _viewController;
            previouseViewController.View.Hidden = true;
            _viewController = _pendingViewController;
            _pendingViewController = null;

            if (_initializingTask.ContainsKey(_viewController))
            {
                var action = _initializingTask[_viewController];
                _initializingTask.Remove(_viewController);
                ActualView.UserInteraction = !IsInitializing;
                action?.Invoke(true);
            }
            else
            {
                if (PageControlDataSource != null) ActualView.PageControl.CurrentPage += (int)_transitionDirection;
                LoadViewControllersIfNeeds();
            }
        }

        void OnCancel()
        {
            _viewController.View.Hidden = false;
            _viewController.BeginAppearanceTransition(true, true);
            _viewController.EndAppearanceTransition();
            _pendingViewController.BeginAppearanceTransition(false, true);
            _pendingViewController.EndAppearanceTransition();
            _pendingViewController.View.Hidden = true;
            _pendingViewController = null;
        }

        void LoadViewControllersIfNeeds()
        {
            if (IsInitializing) return;

            if (_handlingViewControllers.First() == _viewController)
            {
                var first = _handlingViewControllers.First();
                if (first != null)
                {
                    int page = _pageContents[first] - 1;
                    if (0 <= page)
                    {
                        var vc = DataSource?.ViewControllerBefore(this, first);
                        _handlingViewControllers.Insert(0, vc);

                        if (vc != null)
                        {
                            AddChildViewController(page, vc);
                            // vc.View.Hidden = true;
                        }
                    }
                }
            }

            if (_handlingViewControllers.Last() == _viewController)
            {
                var last = _handlingViewControllers.Last();
                if (last != null)
                {
                    int page = _pageContents[last] + 1;
                    if (page < ActualView.PageControl.NumberOfPages)
                    {
                        var vc = DataSource?.ViewControllerAfter(this, last);
                        _handlingViewControllers.Add(vc);

                        if (vc != null)
                        {
                            AddChildViewController(page, vc);
                            // vc.View.Hidden = true;
                        }
                    }
                }
            }
        }
    }
}
