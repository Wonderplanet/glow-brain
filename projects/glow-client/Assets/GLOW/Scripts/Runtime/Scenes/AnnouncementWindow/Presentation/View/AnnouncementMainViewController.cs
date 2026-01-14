using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Enum;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementMainViewController : HomeBaseViewController<AnnouncementMainView>, IEscapeResponder
    {
        public record Argument(AnnouncementDisplayMeansType DisplayMeansType);
        public Action<AlreadyReadAnnouncementFlag> OnCloseCompletion { get; set; }

        [Inject] IAnnouncementMainViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        public AnnouncementContentViewController CurrentContentViewController
        {
            get;
            private set;
        }

        public List<AnnouncementId> ReadInformationAnnouncementIds { get; } = new();
        public List<AnnouncementId> ReadOperationAnnouncementIds { get; } = new();
        public bool Interactable => ActualView.Interactable;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void ShowCurrentContent(AnnouncementContentViewController viewController, AnnouncementTabType currentTabType)
        {
            ShowContentView(viewController, false, false);
            ActualView.SetToggleOn(currentTabType);
            ActualView.Indicator.Hidden = true;
        }

        public void SetTabBadge(AnnouncementMainViewModel viewModel)
        {
            ActualView.SetEventBadgeVisible(HasUnreadAnnouncement(viewModel, AnnouncementTabType.Event));
            ActualView.SetOperationBadgeVisible(HasUnreadAnnouncement(viewModel, AnnouncementTabType.Operation));
        }

        public void SetButtonInteractable(bool interactable)
        {
            ActualView.SetButtonInteractable(interactable);
        }

        public void SetInteractable(bool interactable)
        {
            ActualView.Interactable = interactable;
        }
        
        bool HasUnreadAnnouncement(AnnouncementMainViewModel viewModel, AnnouncementTabType tabType)
        {
            if (tabType == AnnouncementTabType.Event)
            {
                return viewModel.AnnouncementEventViewModel.InformationEventCellViewModels.Any(
                    cell => !(cell.IsRead || ReadInformationAnnouncementIds.Contains(cell.AnnouncementId)));
            }
            else
            {
                return viewModel.AnnouncementOperationViewModel.InformationOperationCellViewModels.Any(
                    cell => !(cell.IsRead || ReadOperationAnnouncementIds.Contains(cell.AnnouncementId)));
            }
        }

        void ShowContentView(AnnouncementContentViewController viewController, bool animated,
            bool worldPositionStays)
        {
            CurrentContentViewController = viewController;

            CurrentContentViewController.View.transform.SetParent(ActualView.ContentRoot, worldPositionStays);
            AddChild(viewController);
            viewController.BeginAppearanceTransition(true, animated);
            viewController.EndAppearanceTransition();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnCloseSelected();
            return true;
        }

        [UIAction]
        void OnEventTabSelected()
        {
            ViewDelegate.OnEventTabSelected();
        }

        [UIAction]
        void OnOperationTabSelected()
        {
            ViewDelegate.OnOperationTabSelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
