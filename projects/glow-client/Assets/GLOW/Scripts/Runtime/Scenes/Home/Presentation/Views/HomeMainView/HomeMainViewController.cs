using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.GameModeSelect.Presentation;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views.HomeMainBanner;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.QuestContentTop.Domain;
using UIKit;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Exceptions;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeMainViewController : HomeBaseViewController<HomeMainView>, IHomeMainViewControl, IEscapeResponder
    {
        [Inject] IHomeMainViewDelegate ViewDelegate { get; }
        [Inject] IStageSelectViewDelegate StageSelectViewDelegate { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] IGameModeSelectViewDelegate GameModeSelectViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyViewDelegate { get; }

        // HomeMainStageSelectControl _stageSelectControl;
        HomeMainStageViewModel _selectedStageModel;

        public bool BannerInitialized => _rotationBannerController != null;
        HomeMainRotationBannerController _rotationBannerController;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            // ActualView.HomeMainQuestView.CarouselView.HapticsPresenter = HapticsPresenter;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();

            //homeMainViewが非表示、かつ一度アプリが非アクティブになると触覚FBが止まるので再開させる
            HapticsPresenter.SyncRestartEngine();
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillDisappear(animated);
            ViewDelegate.OnViewWillDisappear();
        }

        public void InitializeView()
        {
            ActualView.InitializeView();
        }

        public void SetUpHomeMainKoma(
            GameObject patternComponent,
            IReadOnlyList<HomeMainKomaUnitViewModel> unitViewModels)
        {
            ActualView.SetHomeMainKomaPattern(patternComponent, unitViewModels);
        }

        public void SetUpHomeBanner(IReadOnlyList<HomeMainBannerItemViewModel> items)
        {
            if (items.Count <= 0)
            {
                ActualView.BannerPageView.Hidden = true;
                return;
            }

            ActualView.BannerPageView.Hidden = false;
            if (!BannerInitialized || !IsSameBannerViewModels(items))
            {
                SetUpHomeBannerPages(items);
            }
            else
            {
                RestartHomeBanner();
            }
        }

        public void UpdateEventMission(bool isVisible)
        {
            SetEventMissionVisible(isVisible);
        }

        public void SetUpPvpButton(bool isOpen, bool isBadge)
        {
            ActualView.PvpButtonGrayout.SetActive(!isOpen);
            ActualView.PvpButtonBadge.SetActive(isOpen && isBadge);
        }

        public void SetBeginnerMissionVisible(bool isAllCompleted)
        {
            ActualView.BeginnerMissionButton.gameObject.SetActive(!isAllCompleted);
        }

        void SetEventMissionVisible(bool isVisible)
        {
            ActualView.EventMissionButton.IsVisible = isVisible;
        }

        public void SetArtworkPanelMissionVisible(bool isVisible)
        {
            ActualView.ArtworkPanelMissionButton.IsVisible = isVisible;
        }

        public void SetComebackDailyBonusVisible(bool isVisible)
        {
            ActualView.ComebackDailyBonusButton.IsVisible = isVisible;
        }


        (bool shouldShowLeftButton, bool shouldShowRightButton) GetShowButtonStatus(
            HomeMainStageViewModel selected, IReadOnlyList<HomeMainStageViewModel> stages)
        {
            var index = stages.IndexOf(selected);
            return (0 < index, index < stages.Count - 1);
        }


        public void UpdateHomeMainBadge(HomeMainBadgeViewModel viewModel)
        {
            ActualView.DailyMissionBadge.SetActive(viewModel.DailyMission);
            ActualView.BeginnerMissionBadge.SetActive(viewModel.BeginnerMission);
            ActualView.EventMissionBadge.SetActive(viewModel.EventMission);
            ActualView.ArtworkPanelMissionBadge.SetActive(viewModel.ArtworkPanelMission);
            ActualView.EncyclopediaBadge.Hidden = !viewModel.Encyclopedia;
            ActualView.IdleIncentiveBadge.SetActive(viewModel.IdleIncentive);
            ActualView.AnnouncementBadge.SetActive(viewModel.Announcement);
            ActualView.MessageBoxBadge.SetActive(viewModel.MessageBox);
        }

        public void SetFooterOnlyEventBalloon(EventBalloon eventBalloon)
        {
            ActualView.SetEventButton(eventBalloon);

        }

        void SetUpHomeBannerPages(IReadOnlyList<HomeMainBannerItemViewModel> items)
        {
            _rotationBannerController ??= new HomeMainRotationBannerController(
                () => ViewFactory.Create<HomeMainBannerItemViewController>());

            _rotationBannerController.SetUpPages(items, this, ActualView.BannerPageView);
        }

        bool IsSameBannerViewModels(IReadOnlyList<HomeMainBannerItemViewModel> items)
        {
            return _rotationBannerController.IsSameViewModels(items);
        }

        void RestartHomeBanner()
        {
            _rotationBannerController?.Restart();
        }

        public void SetUpHeldPassBanners(IReadOnlyList<HeldPassEffectDisplayViewModel> viewModels)
        {
            ActualView.SetUpHeldPassBanners(viewModels);
        }

        public void UserInteraction(bool interactable)
        {
            ActualView.UserInteraction = interactable;
        }

        void SetActiveStageTryText(DisplayTryStageTextFlag isDisplayTryStageText)
        {
            ActualView.SetVisibleTryStageText(isDisplayTryStageText);
        }

        [UIAction]
        void OnClickMenuButton()
        {
            ViewDelegate.OnMenuSelected();
        }

        [UIAction]
        void OnPvpTopButton()
        {
            ViewDelegate.OnPvpButtonTapped();
        }

        [UIAction]
        void OnClickPassButton()
        {
            NotImpl.Handle();
        }
        [UIAction]
        void OnClickMessageBoxButton()
        {
            ViewDelegate.OnMessageBoxButtonSelected();
        }

        [UIAction]
        void OnClickInformationButton()
        {
            ViewDelegate.OnAnnouncementButtonSelected();
        }

        [UIAction]
        void OnClickBeginnerMissionButton()
        {
            ViewDelegate.OnBeginnerMissionSelected();
        }

        [UIAction]
        void OnClickDailyMissionButton()
        {
            ViewDelegate.OnNormalMissionSelected();
        }

        [UIAction]
        void OnClickEventMissionButton()
        {
            ViewDelegate.OnEventMissionSelected();
        }

        [UIAction]
        void OnClickArtworkPanelMissionButton()
        {
            ViewDelegate.OnArtworkPanelMissionSelected();
        }

        [UIAction]
        void OnItemBoxButtonTapped()
        {
            ViewDelegate.OnItemBoxSelected();
        }

        [UIAction]
        void OnClickMainQuestButton()
        {
            ViewDelegate.OnMainQuestSelected();
        }

        [UIAction]
        void OnClickDeckEditButton()
        {
            ViewDelegate.OnDeckButtonEdit(_selectedStageModel.MstStageId);
        }
        [UIAction]
        void OnClickIdleIncentiveButton()
        {
            ViewDelegate.OnIdleIncentiveSelected();
        }
        [UIAction]
        void OnClickStageStartButton()
        {
            StageSelectViewDelegate.OnStartStageSelected(
                this,
                _selectedStageModel.MstStageId,
                _selectedStageModel.EndAt,
                _selectedStageModel.PlayableFlag,
                _selectedStageModel.StageConsumeStamina,
                _selectedStageModel.DailyClearCount,
                _selectedStageModel.DailyPlayableCount,
                null);
        }


        [UIAction]
        void OnClickLatestEventButton()
        {
             ViewDelegate.OnLatestEventTapped();
        }

        [UIAction]
        void OnEndContentButtonTapped()
        {
            ViewDelegate.OnEndContentButtonTapped();
        }

        [UIAction]
        void OnClickEncyclopediaButton()
        {
            ViewDelegate.OnEncyclopediaTapped();
        }

        [UIAction]
        void OnInGameSpecialRuleButton()
        {
            ViewDelegate.OnInGameSpecialRuleTapped(_selectedStageModel.MstStageId);
        }

        [UIAction]
        void OnComeBackDailyBonusButton()
        {
            ViewDelegate.OnComeBackDailyBonusButtonTapped();
        }

        [UIAction]
        void OnTradeShopButtonTapped()
        {
            ViewDelegate.OnExchangeContentTopButtonTapped();
        }

        [UIAction]
        void OnHomeMainKomaSettingButtonTapped()
        {
            ViewDelegate.OnHomeMainKomaSettingButtonTapped();
        }

        [UIAction]
        void OnOpenMenuButtonTappeed()
        {
            ActualView.VisibleMenuAsync(true, ActualView.GetCancellationTokenOnDestroy()).Forget();
        }

        [UIAction]
        void OnCloseMenuButtonTapped()
        {
            ActualView.VisibleMenuAsync(false, ActualView.GetCancellationTokenOnDestroy()).Forget();
        }

        void IHomeMainViewControl.OnQuestSelected()
        {
            ViewDelegate.OnMainQuestSelected();
        }

        void IHomeMainViewControl.OnQuestSelectedWithId(MasterDataId questId)
        {
            ViewDelegate.OnQuestSelectedWithId(questId);
        }

        void IHomeMainViewControl.OnIdleIncentiveTopSelected()
        {
            ViewDelegate.OnIdleIncentiveSelected();
        }

        void IHomeMainViewControl.OnCharacterListSelected()
        {
            HomeViewDelegate.OnContentSelected(HomeContentTypes.Character);
        }

        void IHomeMainViewControl.OnMissionSelected()
        {
            ViewDelegate.OnNormalMissionSelected();
        }

        void IHomeMainViewControl.OnMissionSelectedForType(MissionType missionType, bool isDisplayFromItemDetail)
        {
            ViewDelegate.OnNormalMissionSelected(missionType, isDisplayFromItemDetail);
        }

        void IHomeMainViewControl.OnBnIdLinkSelected()
        {
            ViewDelegate.OnBnIdLinkSelected();
        }

        void IHomeMainViewControl.OnExchangeContentTopSelected()
        {
            ViewDelegate.OnExchangeContentTopButtonTapped();
        }

        void IHomeMainViewControl.OnExchangeShopTopSelected(MasterDataId mstExchangeId)
        {
            ViewDelegate.OnExchangeShopTopSelected(mstExchangeId);
        }


        bool IEscapeResponder.OnEscape()
        {

            if (TutorialBackKeyViewDelegate.IsPlayingTutorial())
            {
                // トーストでバックキーが無効であると表示する
                CommonToastWireFrame.ShowInvalidOperationMessage();

                return true;
            }

            return false;
        }
    }
}
