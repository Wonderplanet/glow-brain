using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyListComponent : UIBehaviour,
        IUIPageViewControllerDataSource,
        IUIPageViewControllerPageControlDataSource,
        IUIPageViewControllerDelegate
    {
        [SerializeField] UIPageView _partyPageView;
        [SerializeField] RectTransform _partyPageViewContent;
        [SerializeField] UIText _partyName;
        [SerializeField] UIText _totalPartyStatusText;
        [SerializeField] UIObject _totalPartyStatusUpperArrow;
        [SerializeField] Button _prevButton;
        [SerializeField] Button _nextButton;

        List<UIViewController> _partyPageContentViewControllers = new List<UIViewController>();
        UIPageViewController _partyPageViewController;
        PartyNo _presentationPartyNo;

        Action<PartyNo> _switchPartyAction;

        public PartyNo GetCurrentPartyNo()
        {
            var viewController = _partyPageViewController.ViewControllers.First() as PartyFormationPartyViewController;
            return viewController.PartyNo;
        }

        public void InitPageViews(IViewFactory viewFactory,
            UIViewController parentViewController,
            IPartyFormationUnitLongPressDelegate longPressDelegate,
            Action<PartyNo> switchPartyAction,
            PartyFormationInitializeViewModel viewModel,
            MasterDataId specialRuleTargetMstId,
            EventBonusGroupId eventBonusGroupId,
            InGameContentType specialRuleContentType)
        {
            _presentationPartyNo = viewModel.InitialPartyNo;
            _switchPartyAction = switchPartyAction;
            _partyPageViewController = new UIPageViewController();
            _partyPageViewController.View = _partyPageView;
            parentViewController.AddChild(_partyPageViewController);

            _partyPageViewController.ViewDidLoad();
            _partyPageViewController.DataSource = this;
            _partyPageViewController.PageControlDataSource = this;
            _partyPageViewController.Delegate = this;

            PartyFormationPartyViewController initView = null;
            // NOTE: パーティ数を変動させる場合はPresenterからパーティ数を渡す
            for (int i = 0; i < viewModel.ActivePartyCount.Value ; ++i)
            {
                var argument = new PartyFormationPartyViewController.Argument(
                    new PartyNo(i + 1),
                    longPressDelegate,
                    specialRuleTargetMstId,
                    specialRuleContentType,
                    eventBonusGroupId,
                    viewModel.UnitSortFilterCacheType);
                var controller = viewModel.ActivePartyMemberSlotCount <= 5 ?
                    viewFactory.Create<
                        PartyFormationOneLinePartyViewController,
                        PartyFormationPartyViewController.Argument>(argument)
                    : viewFactory.Create<
                        PartyFormationPartyViewController,
                        PartyFormationPartyViewController.Argument>(argument);
                controller.View.Hidden = true;
                controller.View.RectTransform.SetParent(_partyPageViewContent);
                _partyPageContentViewControllers.Add(controller);
                if (viewModel.InitialPartyNo == argument.PartyNo)
                {
                    initView = controller;
                }
            }

            _partyPageViewController.SetViewControllers(
                new List<UIViewController>() { initView },
                UIPageViewController.NavigationDirection.Forward,
                true);

            _partyPageViewController.ActualView.Animate(0);

            var initPageNumber = viewModel.InitialPartyNo;
            UpdatePartyView(initPageNumber);
            _switchPartyAction?.Invoke(initPageNumber);
        }

        public void UpdatePartyView(PartyNo partyNo)
        {
            var page = GetPartyViewController(partyNo);
            page?.UpdateView();
            _partyName.SetText(page?.PartyName.Value);
            _totalPartyStatusText.SetText(page?.TotalPartyStatus.ToStringSeparated());
            _totalPartyStatusUpperArrow.IsVisible = page?.TotalPartyStatusUpperArrowFlag ?? false;
        }

        PartyFormationPartyViewController GetPartyViewController(PartyNo partyNo)
        {
            return _partyPageContentViewControllers.Select(vc => (PartyFormationPartyViewController)vc)
                .FirstOrDefault(vc => vc.PartyNo == partyNo);
        }

        public void OnDragEvent(UserDataId dragUnitId, PointerEventData eventData)
        {
            var partyViewController = _partyPageViewController.ViewControllers.First() as PartyFormationPartyViewController;
            partyViewController?.OnDragEvent(dragUnitId, eventData);
        }

        public void OnDropAvatar(PointerEventData eventData, Action<PartyMemberIndex> onDropAction)
        {
            var partyViewController = _partyPageViewController.ViewControllers.First() as PartyFormationPartyViewController;
            partyViewController?.OnDropAvatar(eventData, onDropAction);
        }

        public void NextPage()
        {
            var nextPage = _partyPageView.PagePosition + 1;
            _partyPageViewController.ActualView.Animate((int)nextPage);
        }

        public void PrevPage()
        {
            var prevPage = _partyPageView.PagePosition - 1;
            _partyPageViewController.ActualView.Animate((int)prevPage);
        }

        public void SetPage(PartyNo partyNo)
        {
            var targetIndex = partyNo.Value - 1;
            if (targetIndex < _partyPageContentViewControllers.Count)
            {
                var targetViewController = _partyPageContentViewControllers[targetIndex];
                _partyPageViewController.SetViewControllers(
                    new List<UIViewController>() { targetViewController },
                    UIPageViewController.NavigationDirection.Forward,
                    false);
                _partyPageView.PageControl.CurrentPage = targetIndex;
                _presentationPartyNo = partyNo;
                UpdatePartyView(partyNo);
                _switchPartyAction?.Invoke(partyNo);
            }
        }

        public void SetPreviewModeInCurrentParty(UserDataId userUnitId, bool isPreview)
        {
            var viewController = _partyPageViewController.ViewControllers.First() as PartyFormationPartyViewController;
            viewController?.SetPreviewMode(userUnitId, isPreview);
        }

        UIViewController IUIPageViewControllerDataSource.ViewControllerAfter(
            UIPageViewController pageViewController,
            UIViewController viewController)
        {
            int index = _partyPageContentViewControllers.IndexOf(viewController);
            if (index < _partyPageContentViewControllers.Count - 1) return _partyPageContentViewControllers[index + 1];
            return _partyPageContentViewControllers.First();
        }

        UIViewController IUIPageViewControllerDataSource.ViewControllerBefore(
            UIPageViewController pageViewController,
            UIViewController viewController)
        {
            int index = _partyPageContentViewControllers.IndexOf(viewController);
            if (index > 0) return _partyPageContentViewControllers[index - 1];
            return _partyPageContentViewControllers.Last();
        }

        int IUIPageViewControllerPageControlDataSource.PresentationCount(UIPageViewController pageViewController)
        {
            return _partyPageContentViewControllers.Count;
        }

        int IUIPageViewControllerPageControlDataSource.PresentationIndex(UIPageViewController pageViewController)
        {
            return _presentationPartyNo.Value - 1;
        }

        void IUIPageViewControllerDelegate.WillTransitionTo(
            UIPageViewController pageViewController,
            UIViewController[] pendingViewControllers)
        {
            _prevButton.interactable = false;
            _nextButton.interactable = false;
            var currentViewController = pageViewController.ViewControllers.First() as PartyFormationPartyViewController;
            currentViewController?.SetScrollMode(true);
            foreach (var viewController in pendingViewControllers)
            {
                if (viewController is PartyFormationPartyViewController partyViewController)
                {
                    partyViewController.SetScrollMode(true);
                }
            }
        }

        void IUIPageViewControllerDelegate.DidFinishAnimating(
            UIPageViewController pageViewController,
            bool finished,
            UIViewController[] previousViewControllers,
            bool transitionCompleted)
        {
            if (finished)
            {
                var partyViewController = pageViewController.ViewControllers.First() as PartyFormationPartyViewController;
                _partyName.SetText(partyViewController?.PartyName.Value);
                _totalPartyStatusText.SetText(partyViewController?.TotalPartyStatus.ToStringSeparated());
                _totalPartyStatusUpperArrow.IsVisible = partyViewController?.TotalPartyStatusUpperArrowFlag ?? false;
                _switchPartyAction?.Invoke(partyViewController?.PartyNo);
                _prevButton.interactable = true;
                _nextButton.interactable = true;
                partyViewController?.SetScrollMode(false);
            }
        }
    }
}
