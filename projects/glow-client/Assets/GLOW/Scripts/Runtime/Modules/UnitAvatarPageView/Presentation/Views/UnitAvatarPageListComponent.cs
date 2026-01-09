using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Modules.UnitAvatarPageView.Presentation.Views
{
    public interface IUnitAvatarPageListDelegate
    {
        void SwitchUnit(MasterDataId mstUnitId);
        void WillTransitionTo();
        void DidFinishAnimating(bool finished, bool transitionCompleted);
    }

    public class UnitAvatarPageListComponent : MonoBehaviour,
        IUIPageViewControllerDelegate,
        IUIPageViewControllerDataSource,
        IUIPageViewControllerPageControlDataSource
    {
        [SerializeField] UIPageView _pageView;
        [SerializeField] RectTransform _pageViewContent;
        [Header("offだと左、onだと右向きに表示される")]
        [SerializeField] bool _defaultFlip;

        UIPageViewController _pageViewController;
        List<UIViewController> _contentViewControllers;

        IReadOnlyList<MasterDataId> _mstUnitIds;
        int _presentationIndex;
        Coroutine _delayFlipCoroutine;
        float _beforePagePosition;
        bool _scrollFinishSeSuppression;

        public IUnitAvatarPageListDelegate Delegate { get; set; }


        void SetFlip(bool isFlip)
        {
            foreach (var vc in _contentViewControllers)
            {
                var avatarPage = vc as UnitAvatarPageViewController;
                avatarPage?.SetFlip(isFlip);
            }
        }

        public void Setup(IViewFactory viewFactory,
            UIViewController parentViewController,
            IReadOnlyList<MasterDataId> mstUnitIds,
            MasterDataId presentationMstUnitId)
        {
            var tempList = new List<MasterDataId>(mstUnitIds);
            // UIPageViewは子要素が3つ未満だと挙動がおかしくなるので、それ以上になるようリストを調整
            while (tempList.Count < 3)
            {
                tempList.AddRange(mstUnitIds);
            }
            _mstUnitIds = mstUnitIds;

            _pageViewController = new UIPageViewController();
            _pageViewController.View = _pageView;
            parentViewController.AddChild(_pageViewController);

            _pageViewController.ViewDidLoad();
            _pageViewController.DataSource = this;
            _pageViewController.PageControlDataSource = this;
            _pageViewController.Delegate = this;

            _contentViewControllers = new List<UIViewController>();
            foreach(var id in tempList)
            {
                var argument = new IUnitAvatarPageViewController.Argument(id);
                var controller = viewFactory
                    .Create<UnitAvatarPageViewController, IUnitAvatarPageViewController.Argument>(argument);
                controller.View.Hidden = true;
                controller.View.RectTransform.SetParent(_pageViewContent);
                _contentViewControllers.Add(controller);
            }

            _pageView.PageControl.NumberOfPages = mstUnitIds.Count;

            _presentationIndex = _mstUnitIds.IndexOf(presentationMstUnitId);

            _pageViewController.SetViewControllers(
                new List<UIViewController> { _contentViewControllers[_presentationIndex] },
                UIPageViewController.NavigationDirection.Forward,
                true
                );

            _pageView.PageControl.CurrentPage = _presentationIndex;
            SetFlip(_defaultFlip);
        }

        public void ScrollToNextPage(bool scrollFinishSeSuppression)
        {
            _scrollFinishSeSuppression = scrollFinishSeSuppression;
            
            var nextPage = _pageView.PagePosition + 1;
            _pageViewController.ActualView.Animate((int)nextPage);
        }

        public void ScrollToPrevPage(bool scrollFinishSeSuppression)
        {
            _scrollFinishSeSuppression = scrollFinishSeSuppression;
            
            var prevPage = _pageView.PagePosition - 1;
            _pageViewController.ActualView.Animate((int)prevPage);
        }

        public void ShowLevelUpAnimation()
        {
            var viewController = _pageViewController.ViewControllers.First() as UnitAvatarPageViewController;
            viewController?.PlayLevelUpAnimation();
        }

        public MasterDataId GetUnitId()
        {
            var currentPage = _pageView.PageControl.CurrentPage;
            return _mstUnitIds[currentPage];
        }

        void IUIPageViewControllerDelegate.WillTransitionTo(
            UIPageViewController pageViewController, 
            UIViewController[] pendingViewControllers)
        {
            var vcList = pendingViewControllers.ToList();
            vcList.Add(pageViewController.ViewControllers.First());
            foreach(var controller in vcList)
            {
                var avatarPage = controller as UnitAvatarPageViewController;
                avatarPage?.PlayMoveAnimation();
            }

            Delegate.WillTransitionTo();

            if (null != _delayFlipCoroutine)
            {
                StopCoroutine(_delayFlipCoroutine);
            }
        }

        void IUIPageViewControllerDelegate.DidFinishAnimating(
            UIPageViewController pageViewController, 
            bool finished, 
            UIViewController[] previousViewControllers, 
            bool transitionCompleted)
        {
            var vcList = previousViewControllers.ToList();
            vcList.Add(pageViewController.ViewControllers.First());
            foreach(var controller in vcList)
            {
                var avatarPage = controller as UnitAvatarPageViewController;
                avatarPage?.PlayWaitAnimation();
            }

            SetFlip(_defaultFlip);

            Delegate.DidFinishAnimating(finished, transitionCompleted);
            if (transitionCompleted)
            {
                if (!_scrollFinishSeSuppression)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                }
                _scrollFinishSeSuppression = false;

                var viewController = pageViewController.ViewControllers.First();
                var index = _contentViewControllers.IndexOf(viewController);
                if(_pageView.PageControl.NumberOfPages == 1)
                {
                    Delegate.SwitchUnit(_mstUnitIds[0]);
                    return;
                }

                if (_pageView.PageControl.NumberOfPages == 2)
                {
                    Delegate.SwitchUnit(_mstUnitIds[index % 2]);
                    return;
                }
                var userUnitId = _mstUnitIds[index];
                Delegate.SwitchUnit(userUnitId);
            }
        }

        UIViewController IUIPageViewControllerDataSource.ViewControllerAfter(
            UIPageViewController pageViewController, 
            UIViewController viewController)
        {
            int index = _contentViewControllers.IndexOf(viewController);
            if (index < _contentViewControllers.Count - 1) return _contentViewControllers[index + 1];
            return _contentViewControllers.First();
        }

        UIViewController IUIPageViewControllerDataSource.ViewControllerBefore(
            UIPageViewController pageViewController, 
            UIViewController viewController)
        {
            int index = _contentViewControllers.IndexOf(viewController);
            if (index > 0) return _contentViewControllers[index - 1];
            return _contentViewControllers.Last();
        }

        int IUIPageViewControllerPageControlDataSource.PresentationCount(UIPageViewController pageViewController)
        {
            return _pageView.PageControl.NumberOfPages;
        }

        int IUIPageViewControllerPageControlDataSource.PresentationIndex(UIPageViewController pageViewController)
        {
            return _contentViewControllers.IndexOf(pageViewController);
        }
    }
}
