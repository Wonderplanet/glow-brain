using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
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
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
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
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.TradeShop.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
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
        [Inject] GetLatestEventUseCase GetLatestEventUseCase { get; }
        [Inject] FetchHomeDataUseCase FetchHomeDataUseCase { get; }
        [Inject] IInAppReviewWireFrame InAppReviewWireFrame { get; }
        [Inject] IHomeAppearanceActionExecutor HomeAppearanceActionExecutor { get; }
        [Inject] AdventBattleRankingResultAction.Factory AdventBattleRankingResultActionFactory { get; }
        [Inject] AnnouncementAction.Factory AnnouncementActionFactory { get; }
        [Inject] DailyBonusAction.Factory DailyBonusActionFactory { get; }
        [Inject] DeferredPurchaseResultAction.Factory DeferredPurchaseResultActionFactory { get; }
        [Inject] EventDailyBonusAction.Factory EventDailyBonusActionFactory { get; }
        [Inject] HeaderExpGaugeAnimationAction.Factory HeaderExpGaugeAnimationActionFactory { get; }
        [Inject] InGameNoticeAction.Factory InGameNoticeActionFactory { get; }
        [Inject] QuestInitializationAction.Factory QuestInitializationActionFactory { get; }
        [Inject] ComebackDailyBonusAction.Factory ComebackDailyBonusActionFactory { get; }
        [Inject] TutorialAction.Factory TutorialActionFactory { get; }
        [Inject] HomeMissionWireFrame HomeMissionWireFrame { get; }
        [Inject] BulkOpenMessageUseCase BulkOpenMessageUseCase { get; }
        [Inject] CheckExistComebackDailyBonusUseCase CheckExistComebackDailyBonusUseCase { get; }

        readonly HomeMainViewModelTranslator _viewModelTranslator = new();

        CancellationToken CancellationToken => HomeViewController.View.GetCancellationTokenOnDestroy();
        CancellationTokenSource _appearanceCancellationTokenSource;
        CancellationTokenSource _badgeUpdateCancellationTokenSource = new();
        CancellationTokenSource _bulkOpenMessageCancellationTokenSource = new();

        bool _showAnimationBefore;  // 一つ前に演出があるかどうか

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
            //NOTE: クエスト・ステージ初期化
            var selectedStageDataModel = StageSelectUseCases.UpdateAndGetQuestUseCaseModel();
            var homeMainViewModel = _viewModelTranslator.TranslateToHomeMainQuestViewModel(selectedStageDataModel);

            // ホームBGM再生
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_home);

            // 選択パーティ表示
            UpdatePartyName();

            // ゲームモード選択イベントバルーン表示
            HomeMainViewController.SetFooterOnlyEventBalloon(GetFooterEventBalloon());

            var latestEvent = GetLatestEventUseCase.GetLatestMstEventModel();
            HomeMainViewController.UpdateEventMission(!latestEvent.IsEmpty());

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

            // 各種ダイアログ表示や演出の非同期処理
            DisposeAppearanceCancellationTokenSource();
            _appearanceCancellationTokenSource = new CancellationTokenSource();

            DoAsync.Invoke(_appearanceCancellationTokenSource.Token, async cancellationToken =>
            {
                await ExecuteHomeAppearanceActions(cancellationToken, homeMainViewModel);

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

        void IHomeMainViewDelegate.OnBeginnerMissionSelected()
        {
            ShowBeginnerMissionView(CancellationToken).Forget();
        }

        void IHomeMainViewDelegate.OnQuestSelected()
        {
            OnQuestSelected();
        }

        void IHomeMainViewDelegate.OnQuestSelectedWithId(MasterDataId questId)
        {
            OnQuestSelectedWithId(questId);
        }

        void IHomeMainViewDelegate.OnQuestInfoClicked(MasterDataId stageId)
        {
            //EventStageSelectPresenterと重複。必要あれば統合
            ShowHomeStageInfoView(stageId);
        }

        void IHomeMainViewDelegate.OnQuestUnReleasedClicked(StageReleaseRequireSentence sentence)
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
            var argument = new HomePartyFormationViewController.Argument(
                selectedMstStageId,
                InGameContentType.Stage,
                EventBonusGroupId.Empty,
                MasterDataId.Empty);
            var controller = ViewFactory
                .Create<HomePartyFormationViewController, HomePartyFormationViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.OnIdleIncentiveSelected(MasterDataId selectedMstStageId)
        {
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

        EventBalloon GetFooterEventBalloon()
        {
            return GetEventNotificationUseCase.GetEventBalloon();
        }

        void OnQuestSelected()
        {
            var controller = ViewFactory.Create<QuestSelectViewController, QuestSelectViewController.Argument>(
                new QuestSelectViewController.Argument(UpdateSelectedStageFromUserProperty, MasterDataId.Empty));
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void OnQuestSelectedWithId(MasterDataId questId)
        {
            var controller = ViewFactory.Create<QuestSelectViewController, QuestSelectViewController.Argument>(
                new QuestSelectViewController.Argument(UpdateSelectedStageFromUserProperty, questId));
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
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
            var latestMstEvent = GetLatestEventUseCase.GetLatestMstEventModel();

            if(latestMstEvent.IsEmpty()) return;//文言とか出す？そもそもボタン非表示？

            var argument = new EventQuestSelectViewController.Argument(latestMstEvent.Id);
            var viewController = ViewFactory
                .Create<EventQuestSelectViewController, EventQuestSelectViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IHomeMainViewDelegate.UpdateSelectedStageFromUserProperty()
        {
            UpdateSelectedStageFromUserProperty();
        }

        void UpdateSelectedStageFromUserProperty()
        {
            // NOTE: ユーザーの選択情報からステージ情報を取得して画面に反映する
            var selectedStageDataModel = StageSelectUseCases.UpdateAndGetQuestUseCaseModel();
            HomeMainViewController.SetQuestViewModel(
                _viewModelTranslator.TranslateToHomeMainQuestViewModel(selectedStageDataModel));
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

        void UpdatePartyName()
        {
            var partyName = GetCurrentPartyNameUseCase.GetCurrentPartyName();
            HomeMainViewController.SetCurrentPartyName(partyName);
        }

        async UniTask ExecuteHomeAppearanceActions(
            CancellationToken cancellationToken,
            HomeMainQuestViewModel homeMainViewModel)
        {
            var displayAtLoginModel = DisplayAtLoginUseCase.CheckDisplayAtLogin();

            // チュートリアルシーケンス中の場合は、以降のActionは実行されない
            if (displayAtLoginModel.PlayingTutorialSequenceFlag) return;

            HomeAppearanceActionExecutor.SetActions(new List<IHomeAppearanceAction>()
            {
                QuestInitializationActionFactory.Create(),
                AdventBattleRankingResultActionFactory.Create(),
                DeferredPurchaseResultActionFactory.Create(),
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
                homeMainViewModel,
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

            var latestMstEvent = GetLatestEventUseCase.GetLatestMstEventModel();
            await HomeMissionWireFrame.ShowEventMissionView(
                missionType,
                latestMstEvent.Id,
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
                var latestMstEventModel = GetLatestEventUseCase.GetLatestMstEventModel();
                HomeMainViewController.UpdateEventMission(!latestMstEventModel.IsEmpty());
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
