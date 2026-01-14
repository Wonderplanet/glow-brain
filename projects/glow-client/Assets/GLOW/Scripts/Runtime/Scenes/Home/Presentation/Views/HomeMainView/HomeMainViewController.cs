using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.GameModeSelect.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views.HomeMainBanner;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using GLOW.Scenes.QuestContentTop.Domain;
using UIKit;
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

        HomeMainStageSelectControl _stageSelectControl;
        HomeMainStageViewModel _selectedStageModel;
        GameModeSelectViewController _gameModeSelectViewController;

        public bool BannerInitialized => _rotationBannerController != null;
        HomeMainRotationBannerController _rotationBannerController;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);

            ActualView.HomeMainQuestView.CarouselView.HapticsPresenter = HapticsPresenter;
            _gameModeSelectViewController = new GameModeSelectViewController();
            _gameModeSelectViewController.Initialize(ActualView.GameModeSelectView, GameModeSelectViewDelegate, () =>
            {
                _gameModeSelectViewController.Close();
                ViewDelegate.UpdateSelectedStageFromUserProperty();
            });
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
            _gameModeSelectViewController?.Close();
        }

        public void InitializeView()
        {
            ActualView.InitializeView();
        }

        public async UniTask InitQuestViewModel(HomeMainQuestViewModel viewModel, CancellationToken cancellationToken)
        {
            InitStageSelectCollectionView(viewModel, cancellationToken);
            RefreshQuest(viewModel);

            // チュートリアル後のステージ未挑戦時の吹き出し表示
            SetActiveStageTryText(viewModel.IsDisplayTryStageText);

            //ステージ開放演出
            //順番依存：CarouselViewのbuild(InitStageSelectCollectionView().CarouselView.DataSource)が呼ばれた後に処理
            await TryShowStageReleaseAnimation(viewModel, cancellationToken);

            //クエスト開放演出
            if (viewModel.ShowQuestReleaseAnimation.ShouldShow)
            {
                await ViewDelegate.ShowQuestReleaseView(
                    viewModel.ShowQuestReleaseAnimation,
                    viewModel.IsInAppReviewDisplay,
                    cancellationToken);
            }
        }

        public void SetQuestViewModel(HomeMainQuestViewModel viewModel)
        {
            RefreshQuest(viewModel);
            RefreshStageCells(viewModel.Stages, viewModel.InitialSelectStageMstStageId);
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

        void InitStageSelectCollectionView(HomeMainQuestViewModel viewModel, CancellationToken cancellationToken)
        {
            var initSelectModel = GetInitSelectViewModel(viewModel.Stages, viewModel.InitialSelectStageMstStageId);

            _stageSelectControl = new HomeMainStageSelectControl(
                ActualView.HomeMainQuestView,
                ActualView.HomeMainQuestView.CarouselView,
                viewModel.Stages,
                initSelectModel,
                OnSelect,
                ViewDelegate
            );
            ActualView.HomeMainQuestView.CarouselView.DataSource = _stageSelectControl;
            ActualView.HomeMainQuestView.CarouselView.ViewDelegate = _stageSelectControl;

            var shouldShowButton = GetShowButtonStatus(initSelectModel, viewModel.Stages);

            OnSelect(initSelectModel,shouldShowButton.shouldShowLeftButton, shouldShowButton.shouldShowRightButton);
        }

        async UniTask TryShowStageReleaseAnimation(HomeMainQuestViewModel viewModel, CancellationToken cancellationToken)
        {
            var shouldShowStageReleaseAnimation = !viewModel.ShowQuestReleaseAnimation.ShouldShow &&
                                                  viewModel.ShowStageReleaseAnimation.ShouldShow;
            ActualView.ReleaseAnimation.gameObject.SetActive(shouldShowStageReleaseAnimation);

            if (!shouldShowStageReleaseAnimation) return;

            var cell = ActualView.HomeMainQuestView.CarouselView.SelectedCell as HomeMainStageSelectCell;
            if (cell == null) throw new Exception("Trying show stage release animation but cell is null.");

            ActualView.ReleaseAnimation.OnStageReleaseEventAction = () => cell.ReleasedGameObject.SetActive(true);
            cell.ReleasedGameObject.SetActive(false);
            await StartStageReleaseAnimation(cancellationToken);
        }

        async UniTask StartStageReleaseAnimation(CancellationToken cancellationToken)
        {
            HomeViewDelegate.ShowTapBlock(true, ActualView.InvertMaskRect, 0f);
            var animationTime = 2.2f;
            var grayOutTransitionStartTime = 1.2f;
            var startDelay = 0.5f;
            await UniTask.Delay(TimeSpan.FromSeconds(startDelay), cancellationToken:cancellationToken);
            ActualView.ReleaseAnimation.ShowAnimation();

            await UniTask.Delay(TimeSpan.FromSeconds(grayOutTransitionStartTime), cancellationToken:cancellationToken);

            var duration = animationTime - grayOutTransitionStartTime;
            HomeViewDelegate.HideTapBlock(true, duration);

            var endDelay = animationTime - startDelay - grayOutTransitionStartTime;
            await UniTask.Delay(TimeSpan.FromSeconds(endDelay), cancellationToken:cancellationToken);
            ActualView.ReleaseAnimation.gameObject.SetActive(false);
        }

        public void SetBeginnerMissionVisible(bool isAllCompleted)
        {
            ActualView.BeginnerMissionButton.gameObject.SetActive(!isAllCompleted);
        }

        public void SetEventMissionVisible(bool isVisible)
        {
            ActualView.EventMissionButton.IsVisible = isVisible;
        }

        public void SetComebackDailyBonusVisible(bool isVisible)
        {
            ActualView.ComebackDailyBonusButton.IsVisible = isVisible;
        }

        void OnSelect(HomeMainStageViewModel selected, bool shouldShowLeftButton, bool shouldShowRightButton)
        {
            _selectedStageModel = selected;
            if (selected.DailyPlayableCount.IsEmpty())
            {
                ActualView.StageConsumeStaminaText.SetText("×{0}", selected.StageConsumeStamina.Value);
            }
            else
            {
                var format = "×{0} あと<color=red>{1}回</color>挑戦可能";
                ActualView.StageConsumeStaminaText.SetText(
                    format,
                    selected.StageConsumeStamina.Value,
                    selected.DailyPlayableCount.Value-selected.DailyClearCount.Value);

            }
            ActualView.HomeMainQuestView.LeftButton.gameObject.SetActive(shouldShowLeftButton);
            ActualView.HomeMainQuestView.RightButton.gameObject.SetActive(shouldShowRightButton);
            ActualView.HomeMainQuestView.SetRecommendedLevel(selected.RecommendedLevel.Value, selected.PlayableFlag);
            ActualView.HomeMainQuestView.SetUpCampaignBalloons(selected.CampaignViewModels);

            UpdateSpeedAttackRecord(selected.SpeedAttackViewModel);
            ActualView.SetOverlappingUIParameters(
                selected.ExistsSpecialRule,
                selected.StaminaBoostBalloonType);
        }

        void RefreshStageCells(IReadOnlyList<HomeMainStageViewModel> stages, MasterDataId selectedStageId)
        {
            var initSelectModel = GetInitSelectViewModel(stages, selectedStageId);

            _stageSelectControl.SetData(stages, initSelectModel);
            ActualView.HomeMainQuestView.CarouselView.ReloadData();

            var shouldShowButton = GetShowButtonStatus(initSelectModel, stages);
            OnSelect(
                initSelectModel,
                shouldShowButton.shouldShowLeftButton,
                shouldShowButton.shouldShowRightButton);
        }

        HomeMainStageViewModel GetInitSelectViewModel(
            IReadOnlyList<HomeMainStageViewModel> stages, MasterDataId selectedStageId)
        {
            return stages.First(s => s.MstStageId == selectedStageId);
        }
        (bool shouldShowLeftButton, bool shouldShowRightButton) GetShowButtonStatus(
            HomeMainStageViewModel selected, IReadOnlyList<HomeMainStageViewModel> stages)
        {
            var index = stages.IndexOf(selected);
            return (0 < index, index < stages.Count - 1);
        }

        void RefreshQuest(HomeMainQuestViewModel viewModel)
        {
            ActualView.HomeMainQuestView.QuestImage.AssetPath = viewModel.QuestImageAssetPath.Value;
            ActualView.HomeMainQuestView.QuestName.SetText(viewModel.QuestName.Value);
            ActualView.HomeMainQuestView.DifficultyLabelComponent.SetDifficulty(viewModel.CurrentDifficulty);
            ActualView.HomeMainQuestView.SetupQuestTimeLimit(viewModel.QuestLimitTime);
        }

        public void UpdateHomeMainBadge(HomeMainBadgeViewModel viewModel)
        {
            ActualView.DailyMissionBadge.SetActive(viewModel.DailyMission);
            ActualView.BeginnerMissionBadge.SetActive(viewModel.BeginnerMission);
            ActualView.EventMissionBadge.SetActive(viewModel.EventMission);
            ActualView.EncyclopediaBadge.Hidden = !viewModel.Encyclopedia;
            ActualView.IdleIncentiveBadge.SetActive(viewModel.IdleIncentive);
            ActualView.AnnouncementBadge.SetActive(viewModel.Announcement);
            ActualView.MessageBoxBadge.SetActive(viewModel.MessageBox);
        }

        public void SetCurrentPartyName(PartyName partyName)
        {
            ActualView.SetCurrentPartyName(partyName);
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

        public void RestartHomeBanner()
        {
            _rotationBannerController?.Restart();
        }

        public void UpdateSpeedAttackRecord(SpeedAttackViewModel viewModel)
        {
            ActualView.SpeedAttackRecord.Hidden = viewModel.IsEmpty();
            ActualView.SpeedAttackRecord.Setup(viewModel.ClearTimeMs, viewModel.NextGoalTime);
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
        void OnQuestSelectButton()
        {
            ViewDelegate.OnQuestSelected();
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
        void OnItemBoxButtonTapped()
        {
            ViewDelegate.OnItemBoxSelected();
        }

        [UIAction]
        void OnClickQuestSelectButton()
        {
            ViewDelegate.OnQuestSelected();
        }

        [UIAction]
        void OnClickDeckEditButton()
        {
            ViewDelegate.OnDeckButtonEdit(_selectedStageModel.MstStageId);
        }
        [UIAction]
        void OnClickIdleIncentiveButton()
        {
            ViewDelegate.OnIdleIncentiveSelected(_selectedStageModel.MstStageId);
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
        void OnRightStageCell()
        {
            if (_stageSelectControl.OnMoveButtonIfNeed(CarouselDirection.Right))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                _stageSelectControl?.MoveRight();
            }
        }
        [UIAction]
        void OnLeftStageCell()
        {
            if ( _stageSelectControl.OnMoveButtonIfNeed(CarouselDirection.Left))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                _stageSelectControl?.MoveLeft();
            }
        }

        [UIAction]
        void OnClickGameModeChange()
        {
             ViewDelegate.OnLatestEventTapped();
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

        void IHomeMainViewControl.OnQuestSelected()
        {
            ViewDelegate.OnQuestSelected();
        }

        void IHomeMainViewControl.OnQuestSelectedWithId(MasterDataId questId)
        {
            ViewDelegate.OnQuestSelectedWithId(questId);
        }

        void IHomeMainViewControl.OnIdleIncentiveTopSelected()
        {
            ViewDelegate.OnIdleIncentiveSelected(_selectedStageModel.MstStageId);
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


            if (!_gameModeSelectViewController.Hidden())
            {
                _gameModeSelectViewController?.Close();
                return true;
            }

            return false;
        }
    }
}
