using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Modules.InAppReview.Presentation;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.ComebackDailyBonus.Presentation.View;
using GLOW.Scenes.EncyclopediaTop.Presentation.Views;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.QuestContentTop.Domain;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.MainQuestTop.Presentation;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.PvpTop.Domain;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.TradeShop.Presentation.View;
using GLOW.Scenes.UnitTab.Domain.UseCase;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.InteractionControls;
using Zenject;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainPresenter : IHomeMainViewDelegate
    {
        [Inject] HomeStageSelectUseCases StageSelectUseCases { get; }
        [Inject] HomeMainBadgeUseCase HomeMainBadgeUseCase { get; }
        [Inject] HomeMainViewController HomeMainViewController { get; }
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] HomeStageInfoUseCases HomeStageInfoUseCases { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] HomeStageInfoViewModelFactory HomeStageInfoViewModelFactory { get; }
        [Inject] UpdateBeginnerMissionAndPassStatusUseCase UpdateBeginnerMissionAndPassStatusUseCase { get; }
        [Inject] IAnnouncementViewFacade AnnouncementViewFacade { get; }
        [Inject] GetEventNotificationUseCase GetEventNotificationUseCase { get; }
        [Inject] HomeMainBannerUseCase HomeMainBannerUseCase { get; }
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] ITutorialFreePartContext TutorialFreePartContext { get; }
        [Inject] DisplayAtLoginUseCase DisplayAtLoginUseCase { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] GetLatestEventInfoUseCase GetLatestEventInfoUseCase { get; }
        [Inject] FetchHomeDataUseCase FetchHomeDataUseCase { get; }
        [Inject] IInAppReviewWireFrame InAppReviewWireFrame { get; }
        [Inject] IHomeAppearanceActionExecutor HomeAppearanceActionExecutor { get; }
        [Inject] AdventBattleRankingResultAction.Factory AdventBattleRankingResultActionFactory { get; }
        [Inject] AnnouncementAction.Factory AnnouncementActionFactory { get; }
        [Inject] DailyBonusAction.Factory DailyBonusActionFactory { get; }
        [Inject] DeferredPurchaseResultAction.Factory DeferredPurchaseResultActionFactory { get; }
        [Inject] WebstorePurchaseProductAction.Factory WebstorePurchaseProductActionFactory { get; }
        [Inject] EventDailyBonusAction.Factory EventDailyBonusActionFactory { get; }
        [Inject] HeaderExpGaugeAnimationAction.Factory HeaderExpGaugeAnimationActionFactory { get; }
        [Inject] InGameNoticeAction.Factory InGameNoticeActionFactory { get; }
        [Inject] ComebackDailyBonusAction.Factory ComebackDailyBonusActionFactory { get; }
        [Inject] TutorialAction.Factory TutorialActionFactory { get; }
        [Inject] HomeMissionWireFrame HomeMissionWireFrame { get; }
        [Inject] BulkOpenMessageUseCase BulkOpenMessageUseCase { get; }
        [Inject] CheckExistComebackDailyBonusUseCase CheckExistComebackDailyBonusUseCase { get; }
        [Inject] PvpStatusUseCase PvpStatusUseCase { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] ITutorialFreePartContext FreePartContext { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] IHomeMainKomaPatternLoader HomeMainKomaPatternLoader { get; }
        [Inject] IHomeMainKomaPatternContainer HomeMainKomaPatternContainer { get; }
        [Inject] HomeMainKomaUseCase HomeMainKomaUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        readonly HomeMainViewModelTranslator _viewModelTranslator = new();

        CancellationToken CancellationToken => HomeViewController.View.GetCancellationTokenOnDestroy();
        CancellationTokenSource _appearanceCancellationTokenSource;
        CancellationTokenSource _badgeUpdateCancellationTokenSource = new();
        CancellationTokenSource _bulkOpenMessageCancellationTokenSource = new();

        MasterDataId _displayComebackDailyBonusScheduleId = MasterDataId.Empty;

        void IHomeMainViewDelegate.OnViewDidLoad()
        {
            HomeMainViewController.InitializeView();
            //NOTE: バッジ表示状態を更新
            //通信の速度次第ではバッジの情報が更新と表示更新のタイミングが前後する可能性がある
            //WillAppearでは通信の後で叩かれている
            //Home.sceneに遷移後初めてホーム訪れたとき、表示遅れが出るのでここで一度叩く
            UpdateHomeMainBadgeStatus();

        }

        void IHomeMainViewDelegate.OnViewWillAppear()
        {
            // ホームBGM再生
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_home);

            // ゲームモード選択イベントバルーン表示
            HomeMainViewController.SetFooterOnlyEventBalloon(GetFooterEventBalloon());

            var latestEventInfo = GetLatestEventInfoUseCase.GetLatestEventInfo();
            HomeMainViewController.UpdateEventMission(!latestEventInfo.LatestMstEventModel.IsEmpty());
            HomeMainViewController.SetArtworkPanelMissionVisible(latestEventInfo.IsArtworkPanelMissionExist);

            // カムバックログインボーナス表示判定
            var comebackDailyBonusModel = CheckExistComebackDailyBonusUseCase.GetCurrentComebackDailyBonus();
            _displayComebackDailyBonusScheduleId = comebackDailyBonusModel.MstComebackDailyBonusScheduleId;

            // スケジュールIDがあって、かつ期間的に有効な場合に表示
            HomeMainViewController.SetComebackDailyBonusVisible(!_displayComebackDailyBonusScheduleId.IsEmpty() &&
                                                                comebackDailyBonusModel.IsVisibleHomeComebackDailyBonusIcon);

            // 初心者ミッション状態更新、バッジ情報取得
            var beginnerMissionAndPassStatus = UpdateBeginnerMissionAndPassStatusUseCase
                .UpdateBeginnerMissionStatusAndGetPassStatus();

            //NOTE: バッジ表示状態を更新
            //順番依存 : 通信の速度次第ではバッジの情報が更新と表示更新のタイミングが前後する可能性があるため通信がある方を後で呼ぶ
            UpdateHomeMainBadgeStatus();

            //NOTE: バッジの定期更新開始
            StartHomeMainBadgeUpdating();

            // 購入したパスを表示する
            var heldPassDisplayViewModels = beginnerMissionAndPassStatus.HeldPassEffectDisplayModels.Select(
                    HeldPassEffectDisplayViewModelTranslator.ToHeldPassEffectDisplayViewModel)
                .ToList();
            HomeMainViewController.SetUpHeldPassBanners(heldPassDisplayViewModels);

            // 初心者ミッション終了時はアイコン非表示
            HomeMainViewController.SetBeginnerMissionVisible(beginnerMissionAndPassStatus.BeginnerMissionFinishedFlag);

            // バナーの初期化、更新
            // ViewDidLoadで呼ぶと、他のViewDidLoadの影響でコルーチンが抜けてしまうためViewWillAppearに記述
            LoadBanners();

            // pvpボタン情報更新
            UpdatePvpButton();

            // コマ設定の反映
            SetUpHomeMainKoma().Forget();

            // 各種ダイアログ表示や演出の非同期処理
            DisposeAppearanceCancellationTokenSource();
            _appearanceCancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_appearanceCancellationTokenSource.Token, async cancellationToken =>
            {
                await ExecuteHomeAppearanceActions(cancellationToken);

                // フリーパートチュートリアル判定
                await CallTutorialFreePartContextLoop(cancellationToken);
            });
        }

        void IHomeMainViewDelegate.OnViewWillDisappear()
        {
            DisposeAppearanceCancellationTokenSource();
            DisposeBadgeUpdateCancellationTokenSource();
            DisposeBulkOpenMessageCancellationTokenSource();
        }

        void IHomeMainViewDelegate.OnEventMissionSelected()
        {
            ShowEventMissionView(MissionType.Event, CancellationToken).Forget();
        }

        void IHomeMainViewDelegate.OnArtworkPanelMissionSelected()
        {
            DoAsync.Invoke(CancellationToken, ScreenInteractionControl, async cancellationToken =>
            {
                await HomeMissionWireFrame.ShowArtworkPanelMissionView(cancellationToken);
            });
        }

        void IHomeMainViewDelegate.OnBeginnerMissionSelected()
        {
            ShowBeginnerMissionView(CancellationToken).Forget();
        }

        void IHomeMainViewDelegate.OnMainQuestSelected()
        {
            OnQuestSelected();
        }

        void IHomeMainViewDelegate.OnQuestSelectedWithId(MasterDataId questId)
        {
            OnQuestSelectedWithId(questId);
        }

        void IHomeMainStageSelectViewDelegate.OnQuestInfoClicked(MasterDataId stageId)
        {
            //EventStageSelectPresenterと重複。必要あれば統合
            ShowHomeStageInfoView(stageId);
        }

        void IHomeMainStageSelectViewDelegate.OnQuestUnReleasedClicked(StageReleaseRequireSentence sentence)
        {
            CommonToastWireFrame.ShowScreenCenterToast(sentence.Value);
        }

        void IHomeMainViewDelegate.OnMenuSelected()
        {
            var controller = ViewFactory.Create<HomeMenuViewController>();
            HomeMainViewController.PresentModally(controller);
        }

        void IHomeMainViewDelegate.OnBnIdLinkSelected()
        {
            var controller = ViewFactory.Create<LinkBnIdDialogViewController>();
            HomeMainViewController.PresentModally(controller);
        }

        void IHomeMainViewDelegate.OnAnnouncementButtonSelected()
        {
            AnnouncementViewFacade.ShowMenuAnnouncement(HomeMainViewController);
        }

        void IHomeMainViewDelegate.OnMessageBoxButtonSelected()
        {
            DisposeBulkOpenMessageCancellationTokenSource();
            _bulkOpenMessageCancellationTokenSource = new CancellationTokenSource();

            var controller = ViewFactory.Create<MessageBoxViewController>();
            controller.OnCloseAction = () =>
            {
                OpenMessage().Forget();
            };

            HomeMainViewController.PresentModally(controller);
        }

        void IHomeMainViewDelegate.OnNormalMissionSelected(MissionType missionType, bool isDisplayFromItemDetail)
        {
            ShowMissionView(false, missionType, isDisplayFromItemDetail, CancellationToken).Forget();
        }

        async UniTask IHomeMainViewDelegate.ShowQuestReleaseView(
            ShowQuestReleaseAnimation showQuestReleaseAnimation,
            InAppReviewFlag isInAppReviewDisplay,
            CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);
            var controller = ViewFactory.Create<QuestReleaseViewController, QuestReleaseViewController.Argument>(
                new QuestReleaseViewController.Argument(
                    showQuestReleaseAnimation.QuestImageAssetPath,
                    showQuestReleaseAnimation.QuestName,
                    showQuestReleaseAnimation.FlavorText));

            controller.OnAnimationCompletion = () =>
            {
                if (!isInAppReviewDisplay) return;

                InAppReviewWireFrame.RequestStoreReview(() => { });
            };

            // 閉じて完了としたいのでUniTaskCompletionSourceを使う
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            controller.OnCloseCompletion = () => { completionSource.TrySetResult(); };

            // IEscapeResponderの関係で、PresentModallyにしてない
            // 別にcontext管理しなくて良いのでShowで表示する
            HomeViewController.Show(controller);

            // 閉じるまで完了を待つ
            await completionSource.Task;
        }

        void IHomeMainViewDelegate.OnItemBoxSelected()
        {
            var controller = ViewFactory.Create<ItemBoxViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnDeckButtonEdit(MasterDataId selectedMstStageId)
        {
            var argument = new HomePartyTabViewController.Argument(
                selectedMstStageId,
                InGameContentType.Stage,
                EventBonusGroupId.Empty,
                MasterDataId.Empty);
            var controller = ViewFactory
                .Create<HomePartyTabViewController, HomePartyTabViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnIdleIncentiveSelected()
        {
            // 現在選択のMstStageIdを取得するUseCase作る
            var selectedMstStageId = StageSelectUseCases.GetInitialSelectMstStageId();
            SelectStageUseCase.SelectStage(selectedMstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty);

            var controller = ViewFactory.Create<IdleIncentiveTopViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnEncyclopediaTapped()
        {
            var controller = ViewFactory.Create<EncyclopediaTopViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnInGameSpecialRuleTapped(MasterDataId selectedMstStageId)
        {
            var controller = ViewFactory
                .Create<InGameSpecialRuleViewController, InGameSpecialRuleViewController.Argument>(
                    new InGameSpecialRuleViewController.Argument(
                        selectedMstStageId,
                        InGameContentType.Stage,
                        InGameSpecialRuleFromUnitSelectFlag.False));
            HomeMainViewController.PresentModally(controller);
        }

        void IHomeMainViewDelegate.OnComeBackDailyBonusButtonTapped()
        {
            ShowComebackDailyBonusView(CancellationToken).Forget();
        }

        void IHomeMainViewDelegate.OnExchangeContentTopButtonTapped()
        {
            var controller = ViewFactory.Create<ExchangeContentTopViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnExchangeShopTopSelected(MasterDataId mstExchangeId)
        {
            var controller = ViewFactory.Create<ExchangeContentTopViewController>();
            HomeViewNavigation.TryPush(
                controller,
                HomeContentDisplayType.BottomOverlap,
                completion: () =>
                {
                    var argument = new ExchangeShopTopViewController.Argument(mstExchangeId);
                    var shopTopVc = ViewFactory.Create<ExchangeShopTopViewController, ExchangeShopTopViewController.Argument>(argument);
                    HomeViewNavigation.TryPush(shopTopVc, HomeContentDisplayType.BottomOverlap);
                });
        }

        void IHomeMainViewDelegate.OnPvpButtonTapped()
        {
            if (IsContentMaintenance(ContentMaintenanceType.Pvp)) return;
            var pvpOpeningStatusModel = PvpStatusUseCase.GetModel();

            if (pvpOpeningStatusModel.PvpQuestContentOpeningStatus.OpeningStatusAtTimeType ==
                QuestContentOpeningStatusAtTimeType.Totalizing)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在ランキング結果の集計中になります\n集計終了までお待ちください");
                return;
            }

            if (pvpOpeningStatusModel.PvpQuestContentOpeningStatus.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Opening)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在、ランクマッチは\n開催されておりません。");
                return;
            }
            if (pvpOpeningStatusModel.PvpQuestContentOpeningStatus.IsLocked())
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    pvpOpeningStatusModel.PvpQuestContentOpeningStatus.QuestContentReleaseRequiredSentence.Value);
                return;
            }

            var viewController = ViewFactory.Create<PvpTopViewController>();
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnHomeMainKomaSettingButtonTapped()
        {
            var viewController = ViewFactory.Create<HomeMainKomaSettingViewController>();
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void UpdatePvpButton()
        {
            var pvpOpeningStatusModel = PvpStatusUseCase.GetModel();
            HomeMainViewController.SetUpPvpButton(
                pvpOpeningStatusModel.PvpQuestContentOpeningStatus.IsOpening(),
                pvpOpeningStatusModel.PvpContentNotification);

        }


        async UniTask SetUpHomeMainKoma()
        {
            var model = HomeMainKomaUseCase.GetHomeMainKomaUseCaseModel();
            var viewModel = HomeMainKomaViewModelTranslator.Translate(model);

            if (!HomeMainKomaPatternContainer.Exists(viewModel.HomeMainKomaPatternAssetPath))
            {
                await HomeMainKomaPatternLoader.Load(
                    HomeMainViewController.ActualView.destroyCancellationToken,
                    viewModel.HomeMainKomaPatternAssetPath);
            }

            var patternComponent =
                HomeMainKomaPatternContainer.Get(viewModel.HomeMainKomaPatternAssetPath);

            HomeMainViewController.SetUpHomeMainKoma(patternComponent, viewModel.HomeMainKomaUnitViewModels);
        }

        bool IsContentMaintenance(ContentMaintenanceType contentMaintenanceType)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(
                    new[] { new ContentMaintenanceTarget(
                        contentMaintenanceType,
                        MasterDataId.Empty) }))
            {
                // チュートリアル再生中であれば、チュートリアルを終了する
                if (FreePartTutorialPlayingStatus.IsPlayingTutorialSequence)
                {
                    FreePartContext.InterruptTutorial();
                }
                ContentMaintenanceWireframe.ShowDialog();
                return true;
            }
            return false;
        }


        EventBalloon GetFooterEventBalloon()
        {
            return GetEventNotificationUseCase.GetEventBalloon();
        }

        void OnQuestSelected()
        {
            var controller = ViewFactory.Create<MainQuestTopViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);

        }

        void OnQuestSelectedWithId(MasterDataId questId)
        {
            var controller = ViewFactory.Create<MainQuestTopViewController>();
            var factory = controller as IMainQuestTopViewFactory;

            // 処理が重いので事前に非表示にしておく
            // 前提：処理がUIKitのTryPopと同じHiddenの操作。MainQuestTopViewの再表示はクエスト選択画面から戻ったときに行われる想定
            factory.VisibleMainQuestTop(false);

            HomeViewNavigation.TryPush(
                controller,
                HomeContentDisplayType.BottomOverlap,
                true,
                () =>
                {
                    factory.ShowQuestSelectView();
                });
        }

        void LoadBanners()
        {
            var banners = HomeMainBannerUseCase.GetHomeMainBannerModels();

            var viewModels = banners
                .Select(HomeMainBannerItemViewModelTranslator.Translate)
                .ToList();

            HomeMainViewController.SetUpHomeBanner(viewModels);
        }

        void IHomeMainViewDelegate.OnLatestEventTapped()
        {
            var latestEventInfo = GetLatestEventInfoUseCase.GetLatestEventInfo();

            if(latestEventInfo.LatestMstEventModel.IsEmpty()) return;//文言とか出す？そもそもボタン非表示？

            var argument = new EventQuestSelectViewController.Argument(latestEventInfo.LatestMstEventModel.Id);
            var viewController = ViewFactory
                .Create<EventQuestSelectViewController, EventQuestSelectViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnEndContentButtonTapped()
        {
            CommonToastWireFrame.ShowScreenCenterToast("近日公開！");
        }

        void UpdateHomeMainBadgeStatus()
        {
            DoAsync.Invoke(HomeViewController.ActualView.destroyCancellationToken, async ct =>
            {
                // ミッション、メールBOXのバッジ・部分メンテ情報を取得＆バッジ更新
                var homeMainBadgeModel = await FetchHomeDataUseCase.UpdateHomeBadgeAndMaintenance(ct);

                HomeMainViewController.UpdateHomeMainBadge(
                    _viewModelTranslator.TranslateToHomeMainBadgeViewModel(homeMainBadgeModel));
            });
        }

        void StartHomeMainBadgeUpdating()
        {
            DisposeBadgeUpdateCancellationTokenSource();
            _badgeUpdateCancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_badgeUpdateCancellationTokenSource.Token, async cancellationToken =>
            {
                using var linkedCancellationTokenSource =
                    CancellationTokenSource.CreateLinkedTokenSource(CancellationToken, cancellationToken);

                var linkedCancellationToken = linkedCancellationTokenSource.Token;

                // NOTE: 毎ループ確認する
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    // NOTE: CancellationTokenがキャンセルされたら処理を終了する
                    if (linkedCancellationToken.IsCancellationRequested)
                    {
                        throw new OperationCanceledException(linkedCancellationToken);
                    }

                    // NOTE: 時間待機（FPSレベルで更新しない）
                    await UniTask.Delay(TimeSpan.FromSeconds(10.0), cancellationToken: linkedCancellationToken);

                    //探索は探索報酬受け取れるようになったらバッジ表示する
                    HomeMainViewController.UpdateHomeMainBadge(
                        _viewModelTranslator.TranslateToHomeMainBadgeViewModel(HomeMainBadgeUseCase.GetHomeMainBadgeModel()));
                }
            });
        }

        async UniTask CallTutorialFreePartContextLoop(CancellationToken cancellationToken)
        {
            while (true)
            {
                var tutorialExecuted = await TutorialFreePartContext.DoIfTutorial(() => UniTask.CompletedTask);

                if (cancellationToken.IsCancellationRequested)
                {
                    throw new OperationCanceledException(cancellationToken);
                }

                if (!tutorialExecuted) break;   // 実行されたチュートリアルがなければ終わり
            }
        }

        async UniTask ExecuteHomeAppearanceActions(
            CancellationToken cancellationToken)
        {
            var displayAtLoginModel = DisplayAtLoginUseCase.CheckDisplayAtLogin();

            // チュートリアルシーケンス中の場合は、以降のActionは実行されない
            if (displayAtLoginModel.PlayingTutorialSequenceFlag) return;

            HomeAppearanceActionExecutor.SetActions(new List<IHomeAppearanceAction>()
            {
                AdventBattleRankingResultActionFactory.Create(),
                DeferredPurchaseResultActionFactory.Create(),   // 遅延決済
                WebstorePurchaseProductActionFactory.Create(),  // 外部決済
                TutorialActionFactory.Create(),     // 強制チュートリアルがある場合は以降のActionは実行されない
                ComebackDailyBonusActionFactory.Create(), // カムバック > 通常ログインボーナス > イベントログインボーナスの順
                DailyBonusActionFactory.Create(),   // 挑戦するボタンを押した場合は以降のActionは実行されない
                EventDailyBonusActionFactory.Create(),  // 挑戦するボタンを押した場合は以降のActionは実行されない
                AnnouncementActionFactory.Create(),
                InGameNoticeActionFactory.Create(),  // 遷移ボタンを押した場合は以降のActionは実行されない
                HeaderExpGaugeAnimationActionFactory.Create(),
            });

            await HomeAppearanceActionExecutor.ExecuteActions(
                cancellationToken,
                displayAtLoginModel,
                CreateEventMissionOnCloseCompletion());
        }

        async UniTask ShowMissionView(
            bool isFirstLogin,
            MissionType missionType,
            bool isDisplayFromItemDetail,
            CancellationToken cancellationToken)
        {
            await HomeMissionWireFrame.ShowMissionView(isFirstLogin, missionType, isDisplayFromItemDetail, cancellationToken);
        }

        async UniTask ShowBeginnerMissionView(CancellationToken cancellationToken)
        {
            await HomeMissionWireFrame.ShowBeginnerMissionView(cancellationToken);
        }

        async UniTask ShowComebackDailyBonusView(CancellationToken cancellationToken)
        {
            await HomeMissionWireFrame.ShowComebackDailyBonusView(_displayComebackDailyBonusScheduleId, cancellationToken);
        }

        async UniTask ShowEventMissionView(MissionType missionType, CancellationToken cancellationToken)
        {
            var onCloseCompletion = new Action(() =>
            {
                HomeMainViewController.UpdateHomeMainBadge(
                    _viewModelTranslator.TranslateToHomeMainBadgeViewModel(HomeMainBadgeUseCase.GetHomeMainBadgeModel()));
            });

            var latestEventInfo = GetLatestEventInfoUseCase.GetLatestEventInfo();
            await HomeMissionWireFrame.ShowEventMissionView(
                missionType,
                latestEventInfo.LatestMstEventModel.Id,
                onCloseCompletion,
                cancellationToken);
        }

        Action CreateEventMissionOnCloseCompletion()
        {
            var completionSource = new UniTaskCompletionSource();
            return () =>
            {
                HomeMainViewController.UpdateHomeMainBadge(
                    _viewModelTranslator.TranslateToHomeMainBadgeViewModel(HomeMainBadgeUseCase.GetHomeMainBadgeModel()));
                completionSource.TrySetResult();
                var latestEventInfo = GetLatestEventInfoUseCase.GetLatestEventInfo();
                HomeMainViewController.UpdateEventMission(!latestEventInfo.LatestMstEventModel.IsEmpty());
            };
        }

        void ShowHomeStageInfoView(MasterDataId stageId)
        {
            DoAsync.Invoke(HomeMainViewController.ActualView, async ct =>
            {
                var homeStageInfoUseCaseModel = HomeStageInfoUseCases.GetHomeStageInfoUseCasesModel(stageId);
                var viewModel = HomeStageInfoViewModelFactory.ToHomeStageInfoViewModel(homeStageInfoUseCaseModel);

                // 画面がチラつくので1フレーム待つ
                await UniTask.Delay(1, cancellationToken: ct);

                var controller = ViewFactory.Create<HomeStageInfoViewController, HomeStageInfoViewController.Argument>(
                    new HomeStageInfoViewController.Argument(viewModel));
                controller.ReopenStageInfoAction = () =>
                {
                    if (HomeViewController.ViewContextController.CurrentContentType == HomeContentTypes.Main)
                    {
                        ShowHomeStageInfoView(stageId);
                    }
                };
                HomeMainViewController.PresentModally(controller);
            });
        }

        async UniTask OpenMessage()
        {
            await BulkOpenMessageUseCase.OpenAndUpdateMessages(_bulkOpenMessageCancellationTokenSource.Token,
                new List<MasterDataId>());

            // メールBOXのアイコンにバッジ反映する
            HomeMainViewController.UpdateHomeMainBadge(
                _viewModelTranslator.TranslateToHomeMainBadgeViewModel(HomeMainBadgeUseCase.GetHomeMainBadgeModel()));
        }

        void DisposeAppearanceCancellationTokenSource()
        {
            _appearanceCancellationTokenSource?.Cancel();
            _appearanceCancellationTokenSource?.Dispose();
            _appearanceCancellationTokenSource = null;
        }

        void DisposeBadgeUpdateCancellationTokenSource()
        {
            _badgeUpdateCancellationTokenSource?.Cancel();
            _badgeUpdateCancellationTokenSource?.Dispose();
            _badgeUpdateCancellationTokenSource = null;
        }

        void DisposeBulkOpenMessageCancellationTokenSource()
        {
            _bulkOpenMessageCancellationTokenSource?.Cancel();
            _bulkOpenMessageCancellationTokenSource?.Dispose();
            _bulkOpenMessageCancellationTokenSource = null;
        }
    }
}
