using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkPageComponent : MonoBehaviour,
        IUIPageViewControllerDelegate,
        IUIPageViewControllerDataSource,
        IUIPageViewControllerPageControlDataSource
    {
        [SerializeField] UIPageView pageView;
        [SerializeField] RectTransform pageViewContent;

        UIPageViewController _pageViewController;
        List<UIViewController> _contentViewControllers;

        IReadOnlyList<MasterDataId> _mstArtworkIds;
        int _presentationIndex;
        bool _scrollFinishSeSuppression;

        public IEncyclopediaArtworkPageListDelegate Delegate { get; set; }

        public void Setup(IViewFactory viewFactory,
            UIViewController parentViewController,
            IReadOnlyList<MasterDataId> mstArtworkIds,
            MasterDataId presentationMstArtworkId,
            Action<MasterDataId> onSelectArtworkExpand)
        {
            var tempList = new List<MasterDataId>(mstArtworkIds);
            // UIPageViewは子要素が3つ未満だと挙動がおかしくなるので、それ以上になるようリストを調整
            while (tempList.Count < 3)
            {
                tempList.AddRange(mstArtworkIds);
            }
            _mstArtworkIds = mstArtworkIds;

            _pageViewController = new UIPageViewController();
            _pageViewController.View = pageView;
            parentViewController.AddChild(_pageViewController);

            _pageViewController.ViewDidLoad();
            _pageViewController.DataSource = this;
            _pageViewController.PageControlDataSource = this;
            _pageViewController.Delegate = this;

            _contentViewControllers = new List<UIViewController>();
            foreach (var id in tempList)
            {
                var argument = new EncyclopediaArtworkPageViewController.Argument(id);
                var controller = viewFactory
                    .Create<EncyclopediaArtworkPageViewController, EncyclopediaArtworkPageViewController.Argument>(argument);
                controller.View.Hidden = true;
                controller.View.RectTransform.SetParent(pageViewContent);
                controller.OnSelectArtworkExpand = onSelectArtworkExpand;
                _contentViewControllers.Add(controller);
            }

            pageView.PageControl.NumberOfPages = mstArtworkIds.Count;

            _presentationIndex = _mstArtworkIds.IndexOf(presentationMstArtworkId);

            _pageViewController.SetViewControllers(
                new List<UIViewController> { _contentViewControllers[_presentationIndex] },
                UIPageViewController.NavigationDirection.Forward,
                true
                );

            pageView.PageControl.CurrentPage = _presentationIndex;

            // 作品が2つ以上の時のみドラッグ可能にする
            pageView.CanDrag = mstArtworkIds.Count > 1;
        }

        public void ScrollToNextPage(bool scrollFinishSeSuppression)
        {
            _scrollFinishSeSuppression = scrollFinishSeSuppression;

            var nextPage = pageView.PagePosition + 1;
            _pageViewController.ActualView.Animate((int)nextPage);
        }

        public void ScrollToPrevPage(bool scrollFinishSeSuppression)
        {
            _scrollFinishSeSuppression = scrollFinishSeSuppression;

            var prevPage = pageView.PagePosition - 1;
            _pageViewController.ActualView.Animate((int)prevPage);
        }

        public MasterDataId GetArtworkId()
        {
            var currentPage = pageView.PageControl.CurrentPage;
            return _mstArtworkIds[currentPage];
        }

        void IUIPageViewControllerDelegate.WillTransitionTo(
            UIPageViewController pageViewController,
            UIViewController[] pendingViewControllers)
        {
            Delegate?.WillTransitionTo();
        }

        void IUIPageViewControllerDelegate.DidFinishAnimating(
            UIPageViewController pageViewController,
            bool finished,
            UIViewController[] previousViewControllers,
            bool transitionCompleted)
        {
            Delegate?.DidFinishAnimating(finished, transitionCompleted);
            if (transitionCompleted)
            {
                if (!_scrollFinishSeSuppression)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                }
                _scrollFinishSeSuppression = false;

                var viewController = pageViewController.ViewControllers.First();
                var index = _contentViewControllers.IndexOf(viewController);
                if (pageView.PageControl.NumberOfPages == 1)
                {
                    Delegate?.SwitchArtwork(_mstArtworkIds[0]);
                    return;
                }

                if (pageView.PageControl.NumberOfPages == 2)
                {
                    Delegate?.SwitchArtwork(_mstArtworkIds[index % 2]);
                    return;
                }
                var artworkId = _mstArtworkIds[index];
                Delegate?.SwitchArtwork(artworkId);
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
            return pageView.PageControl.NumberOfPages;
        }

        int IUIPageViewControllerPageControlDataSource.PresentationIndex(UIPageViewController pageViewController)
        {
            return _contentViewControllers.IndexOf(pageViewController.ViewControllers.First());
        }
    }
}
