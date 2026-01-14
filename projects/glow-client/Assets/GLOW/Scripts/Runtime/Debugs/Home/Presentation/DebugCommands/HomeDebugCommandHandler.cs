#if GLOW_DEBUG
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.LocalNotification;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Core.Presentation.Transitions;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Command.Presentations.Views;
using GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView;
using GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView;
using GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView;
using GLOW.Debugs.DebugGrid.Presentation.Views;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.Home.Domain.Models;
using GLOW.Debugs.Home.Domain.UseCases;
using GLOW.Debugs.InGame.Domain.Definitions;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.InAppReview.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Views;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Translator;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.BoxGachaResult.Presentation.View;
using GLOW.Scenes.BoxGachaResult.Presentation.ViewModel;
using GLOW.Scenes.DebugStageDetail.Domain;
using GLOW.Scenes.GachaResult.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.Notice.Presentation.Facade;
using GLOW.Scenes.Notice.Presentation.ViewModel;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using GLOW.Scenes.PvpBattleResult.Presentation.ViewModel;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.ViewModels;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.Views;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.ViewModels;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using GLOW.Scenes.PvpRanking.Presentation.ViewModels;
using GLOW.Scenes.PvpRanking.Presentation.Views;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WonderPlanet.SceneManagement;
using WonderPlanet.ToastNotifier;
using Wonderplanet.UIHaptics;
using Wonderplanet.UIHaptics.Presentation;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Debugs.Profiler;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Debugs.Home.Presentation.DebugCommands
{
    public sealed class HomeDebugCommandHandler
    {
        [Inject] HomeViewController ViewController { get; }
        [Inject] IHomeUseCases UseCases { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] INoticeViewFacade NoticeViewFacade { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] DisableInGameApiCallDebugUseCase DisableInGameApiCallDebugUseCase { get; }
        [Inject] GetAllStagesDebugUseCase GetAllStagesDebugUseCase { get; }
        [Inject] Context Context { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] ProgressTutorialStatusUseCase ProgressTutorialStatusUseCase { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IDeferredPurchaseCacheRepository DeferredPurchaseCacheRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IMstShopProductDataRepository MstShopProductDataRepository { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] IInAppReviewWireFrame InAppReviewWireFrame { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }
        [Inject] IUserLevelUpResultViewFacade UserLevelUpResultViewFacade { get; }
        [Inject] DebugSetMyPartyToPvpOpponentUseCase DebugSetMyPartyToPvpOpponentUseCase { get; }
        [Inject] DebugMstUnitStatusUseCase DebugMstUnitStatusUseCase { get; }
        [Inject] DebugStageSummaryUseCase DebugStageSummaryUseCase { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] UnitTemporaryParameterDebugCommand UnitTemporaryParameterDebugCommand{ get; }
        [Inject] IArtworkFragmentAcquisitionModelFactory ArtworkFragmentAcquisitionModelFactory { get; }

        public Action OnBackTitle { get; set; }


        public void CreateDebugCommandRootMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "タイトル画面に戻る",
                () => { OnBackTitle(); });
            debugCommandPresenter.AddButton(
                "汎用ダイアログ（2ボタン）表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    // 汎用ダイアログ 表示確認のため
                    MessageViewUtil.ShowConfirmMessage(
                        "タイトル",
                        "メッセージ1行目\nメッセージ2行目\nメッセージ3行目",
                        "警告メッセージ1行目\n警告メッセージ2行目\n警告メッセージ3行目",
                        () => { },
                        () => { });
                });

            debugCommandPresenter.AddButton(
                "汎用ダイアログ（OKボタン）表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    // 汎用ダイアログ 表示確認のため
                    MessageViewUtil.ShowMessageWithOk(
                        "タイトル",
                        "メッセージ1行目\nメッセージ2行目\nメッセージ3行目",
                        string.Empty,
                        () => { });
                });

            debugCommandPresenter.AddButton(
                "汎用ダイアログ（閉じるボタン）表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    // 汎用ダイアログ 表示確認のため
                    MessageViewUtil.ShowMessageWithClose(
                        "タイトル",
                        "メッセージ1行目\nメッセージ2行目\nメッセージ3行目",
                        string.Empty,
                        () => { });
                });
            debugCommandPresenter.AddButton(
                "IGN 表示(ガチャ一覧)",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    var viewModel = new NoticeViewModel(
                        new NoticeId("1"),
                        IgnDisplayType.BasicBanner,
                        IgnDisplayFrequencyType.Always,
                        new NoticeTitle("IGN バナーダウンロードテスト"),
                        new NoticeMessage("IGN バナーダウンロードテスト"),
                        new NoticeBannerUrl("ign/Banner_L.png"),
                        new NoticeDestinationType("InGame"),
                        new DestinationScene("Gacha"),
                        NoticeDestinationPathDetail.Empty,
                        new NoticeTransitionButtonText("ガチャへ"));
                    NoticeViewFacade.ShowInGameNoticeWithBannerDownload(viewModel);
                });
            debugCommandPresenter.AddButton(
                "IGN 表示(外部サイト)",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    var viewModel = new NoticeViewModel(
                        new NoticeId("1"),
                        IgnDisplayType.BasicBanner,
                        IgnDisplayFrequencyType.Always,
                        new NoticeTitle("IGN バナーダウンロードテスト"),
                        new NoticeMessage("IGN バナーダウンロードテスト"),
                        new NoticeBannerUrl("ign/Banner_L.png"),
                        new NoticeDestinationType("Web"),
                        new DestinationScene("https://www.youtube.com/@jumpchannel"),
                        NoticeDestinationPathDetail.Empty,
                        new NoticeTransitionButtonText("サイトへ"));
                    NoticeViewFacade.ShowInGameNoticeWithBannerDownload(viewModel);
                });
            debugCommandPresenter.AddButton(
                "ガチャコンテンツ表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    HomeViewControl.OnGachaContentSelectedFromHome(new MasterDataId("4"));
                });
            debugCommandPresenter.AddButton(
                "ランクマッチ(Pvp)インゲーム遷移",
                () => { StartPvpForDebug(debugCommandPresenter); });
            debugCommandPresenter.AddButton(
                "プレイアブルキャラの敵モードでのモーション確認用",
                () => { StartPvpVsMyPartyForDebug(debugCommandPresenter); });
            debugCommandPresenter.AddButton(
                "決闘リザルト表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    var viewModel = new PvpBattleResultPointViewModel(
                        PvpRankClassType.Bronze,
                        new PvpRankLevel(1),
                        new List<PvpBattleResultPointRankTargetViewModel>
                        {
                            new PvpBattleResultPointRankTargetViewModel(
                                new PvpPoint(330),
                                new PvpPoint(400),
                                new PvpPoint(400),
                                PvpRankClassType.Bronze,
                                new PvpRankLevel(2),
                                new PvpBattleResultRankAnimationGaugeRate(0.3f),
                                new PvpBattleResultRankAnimationGaugeRate(1.0f)),
                            new PvpBattleResultPointRankTargetViewModel(
                                new PvpPoint(400),
                                new PvpPoint(420),
                                new PvpPoint(500),
                                PvpRankClassType.Bronze,
                                new PvpRankLevel(3),
                                new PvpBattleResultRankAnimationGaugeRate(0.0f),
                                new PvpBattleResultRankAnimationGaugeRate(0.2f))
                        },
                        new PvpPoint(50),
                        new PvpPoint(10),
                        new PvpPoint(30),
                        new PvpPoint(420));
                    var argument = new PvpBattleResultViewController.Argument(viewModel);
                    var controller =
                        ViewFactory.Create<PvpBattleResultViewController, PvpBattleResultViewController.Argument>(
                            argument);
                    ViewController.PresentModally(controller);
                });
            debugCommandPresenter.AddButton(
                "決闘フィニッシュ",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    var argument = new PvpBattleFinishAnimationViewController.Argument(
                        new PvpResultViewModel(
                            PvpResultEvaluator.PvpResultType.Victory,
                            new PvpMaxDistanceRatio(0.5f),
                            new PvpMaxDistanceRatio(0.5f),
                            PvpResultEvaluator.PvpFinishType.MaxDistance));
                    var controller =
                        ViewFactory
                            .Create<PvpBattleFinishAnimationViewController,
                                PvpBattleFinishAnimationViewController.Argument>(argument);
                    ViewController.PresentModally(controller);
                });
            debugCommandPresenter.AddNestedMenuButton(
                ">> ユニットモーション確認",
                debugCommandPresenter =>
                    UnitTemporaryParameterDebugCommand.CreateTemporaryParameterMenu(debugCommandPresenter,
                        () =>
                        {
                            StartStageForDebug(debugCommandPresenter, GetAllStagesDebugUseCase.GetAllStages()[0].MstStageModel.Id);
                        }));
            debugCommandPresenter.AddNestedMenuButton(
                ">> ステージ",
                CreateDebugStageSelectMenu);
            debugCommandPresenter.AddNestedMenuButton(
                ">> ユニットマスターデータチェック",
                CreateUnitCheckSelectMenu);
            debugCommandPresenter.AddNestedMenuButton(
                ">> Stageマスターデータチェック",
                CreateStageCheckSelectMenu);
            debugCommandPresenter.AddButton(
                "決闘前シーズン結果演出",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    var diamondAssetPath = DiamondIconAssetPath
                        .FromAssetKey(new DiamondAssetKey().ToPlayerResourceAssetKey())
                        .ToPlayerResourceIconAssetPath();
                    var argument = new PvpPreviousSeasonResultViewController.Argument(
                        new PvpPreviousSeasonResultViewModel(
                            PvpRankClassType.Platinum,
                            new PvpRankLevel(2),
                            new PvpPoint(1234567890),
                            new PvpRankingRank(4),
                            new List<PlayerResourceIconViewModel>()
                            {
                                PlayerResourceIconViewModel.Empty with
                                {
                                    AssetPath = diamondAssetPath,
                                    ResourceType = ResourceType.FreeDiamond,
                                    Amount = new PlayerResourceAmount(100)
                                },
                                PlayerResourceIconViewModel.Empty with
                                {
                                    AssetPath = diamondAssetPath,
                                    ResourceType = ResourceType.FreeDiamond,
                                    Amount = new PlayerResourceAmount(20)
                                },
                                PlayerResourceIconViewModel.Empty with
                                {
                                    AssetPath = diamondAssetPath,
                                    ResourceType = ResourceType.FreeDiamond,
                                    Amount = new PlayerResourceAmount(3000)
                                },
                            })
                    );
                    var controller =
                        ViewFactory
                            .Create<PvpPreviousSeasonResultViewController,
                                PvpPreviousSeasonResultViewController.Argument>(argument);
                    ViewController.PresentModally(controller);
                });

            debugCommandPresenter.AddButton(
                "決闘新シーズン開始演出",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    var argument = new PvpNewSeasonStartViewController.Argument(
                        new PvpNewSeasonStartViewModel(
                            PvpRankClassType.Platinum,
                            new ScoreRankLevel(2)
                        ));
                    var controller =
                        ViewFactory.Create<PvpNewSeasonStartViewController, PvpNewSeasonStartViewController.Argument>(
                            argument);
                    ViewController.PresentModally(controller);
                });

            debugCommandPresenter.AddButton(
                "決闘ランキングチェック",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    PvpRankingViewModel pvpRankingViewModel = PvpRankingViewModel.Empty with
                    {
                        CurrentRanking = PvpRankingElementViewModel.Empty with
                        {
                            OtherUserViewModels = new List<PvpRankingOtherUserViewModel>()
                            {
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("テストユーザー1"),
                                    Score = new PvpPoint(1000),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(1),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Platinum,
                                    RankLevel = new PvpRankLevel(4),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Platinum, new PvpTier(4))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("テストユーザー2"),
                                    Score = new PvpPoint(900),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(2),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Gold,
                                    RankLevel = new PvpRankLevel(3),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Gold, new PvpTier(3))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("テストユーザー3"),
                                    Score = new PvpPoint(800),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(3),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Silver,
                                    RankLevel = new PvpRankLevel(2),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Silver, new PvpTier(2))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("テストユーザー4"),
                                    Score = new PvpPoint(700),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(4),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Bronze,
                                    RankLevel = new PvpRankLevel(1),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Bronze, new PvpTier(1))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("テストユーザー5"),
                                    Score = new PvpPoint(600),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(5),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Bronze,
                                    RankLevel = new PvpRankLevel(1),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Bronze, new PvpTier(1))
                                },
                            },
                            MyselfUserViewModel = PvpRankingMyselfUserViewModel.Empty with
                            {
                                UserName = new UserName("テストユーザー1"),
                                Score = new PvpPoint(1000),
                                EmblemIconAssetPath =
                                EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                UnitIconAssetPath = CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                Rank = new PvpRankingRank(4),
                                RankClassType = PvpRankClassType.Platinum,
                                RankLevel = new PvpRankLevel(4),
                                PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Platinum, new PvpTier(4))
                            }
                        },
                        PrevRanking = PvpRankingElementViewModel.Empty with
                        {
                            OtherUserViewModels = new List<PvpRankingOtherUserViewModel>()
                            {
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("過去テストユーザー1"),
                                    Score = new PvpPoint(1000),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(1),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Platinum,
                                    RankLevel = new PvpRankLevel(4),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Platinum, new PvpTier(4))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("過去テストユーザー2"),
                                    Score = new PvpPoint(900),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(2),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Gold,
                                    RankLevel = new PvpRankLevel(3),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Gold, new PvpTier(3))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("過去テストユーザー3"),
                                    Score = new PvpPoint(800),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(3),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Silver,
                                    RankLevel = new PvpRankLevel(2),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Silver, new PvpTier(2))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("過去テストユーザー4"),
                                    Score = new PvpPoint(700),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(4),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Bronze,
                                    RankLevel = new PvpRankLevel(1),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Bronze, new PvpTier(1))
                                },
                                PvpRankingOtherUserViewModel.Empty with
                                {
                                    UserName = new UserName("過去テストユーザー5"),
                                    Score = new PvpPoint(600),
                                    EmblemIconAssetPath =
                                    EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                    UnitIconAssetPath =
                                    CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                    Rank = new PvpRankingRank(5),
                                    IsMyself = PvpRankingMyselfFlag.False,
                                    RankClassType = PvpRankClassType.Bronze,
                                    RankLevel = new PvpRankLevel(1),
                                    PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Bronze, new PvpTier(1))
                                },
                            },
                            MyselfUserViewModel = PvpRankingMyselfUserViewModel.Empty with
                            {
                                UserName = new UserName("過去テストユーザー4"),
                                Score = new PvpPoint(1000),
                                EmblemIconAssetPath =
                                EmblemIconAssetPath.FromAssetKey(new EmblemAssetKey("test_emblem")),
                                UnitIconAssetPath = CharacterIconAssetPath.FromAssetKey(new UnitAssetKey("test_unit")),
                                Rank = new PvpRankingRank(4),
                                RankClassType = PvpRankClassType.Bronze,
                                RankLevel = new PvpRankLevel(1),
                                PvpUserRankStatus = new PvpUserRankStatus(PvpRankClassType.Bronze, new PvpTier(1))
                            }
                        }
                    };
                    var argument = new PvpRankingViewController.Argument(pvpRankingViewModel);
                    var controller =
                        ViewFactory.Create<PvpRankingViewController, PvpRankingViewController.Argument>(argument);
                    ViewController.PresentModally(controller);
                });

#if UNITY_IAP_FAKESTORE // デバッグ課金時のみ表示
            debugCommandPresenter.AddNestedMenuButton(
                "課金テスト機能切り替え",
                CreateFakeStoreSelectMenu);
#endif

            debugCommandPresenter.AddButton(
                "遅延決済報酬追加",
                () =>
                {
                    var mstStoreProduct = MstShopProductDataRepository.GetStoreProducts().First();
                    var dummyUserStoreProduct = new UserStoreProductModel(
                        mstStoreProduct.OprProductId,
                        new PurchaseCount(1),
                        new PurchaseCount(1));

                    // 有償石100, コイン1000, オカルン, ターボババアオカルン(ダブり), 桃(ダブり), 姫様(ダブり), トーチャー(ダブり)
                    DeferredPurchaseCacheRepository.AddDeferredPurchaseResult(
                        new PurchaseResultCacheModel(
                            new List<RewardModel>()
                            {
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.PaidDiamond,
                                    ResourceId = MasterDataId.Empty,
                                    Amount = new PlayerResourceAmount(100)
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Coin,
                                    ResourceId = MasterDataId.Empty,
                                    Amount = new PlayerResourceAmount(1000)
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Unit,
                                    ResourceId = new MasterDataId("chara_dan_00001"),
                                    Amount = new PlayerResourceAmount(1)
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Item,
                                    ResourceId = new MasterDataId("piece_dan_00002"),
                                    Amount = new PlayerResourceAmount(50),
                                    PreConversionResource = PreConversionResourceModel.Empty with
                                    {
                                        ResourceType = ResourceType.Unit,
                                        ResourceId = new MasterDataId("chara_dan_00002"),
                                        ResourceAmount = new ObscuredPlayerResourceAmount(1)
                                    }
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Item,
                                    ResourceId = new MasterDataId("piece_dan_00101"),
                                    Amount = new PlayerResourceAmount(50),
                                    PreConversionResource = PreConversionResourceModel.Empty with
                                    {
                                        ResourceType = ResourceType.Unit,
                                        ResourceId = new MasterDataId("chara_dan_00101"),
                                        ResourceAmount = new ObscuredPlayerResourceAmount(1)
                                    }
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Item,
                                    ResourceId = new MasterDataId("piece_gom_00001"),
                                    Amount = new PlayerResourceAmount(50),
                                    PreConversionResource = PreConversionResourceModel.Empty with
                                    {
                                        ResourceType = ResourceType.Unit,
                                        ResourceId = new MasterDataId("chara_gom_00001"),
                                        ResourceAmount = new ObscuredPlayerResourceAmount(1)
                                    }
                                },
                                RewardModel.Empty with
                                {
                                    ResourceType = ResourceType.Item,
                                    ResourceId = new MasterDataId("piece_gom_00101"),
                                    Amount = new PlayerResourceAmount(50),
                                    PreConversionResource = PreConversionResourceModel.Empty with
                                    {
                                        ResourceType = ResourceType.Unit,
                                        ResourceId = new MasterDataId("chara_gom_00101"),
                                        ResourceAmount = new ObscuredPlayerResourceAmount(1)
                                    }
                                },
                            },
                            dummyUserStoreProduct)
                    );
                }
            );

            debugCommandPresenter.AddNestedMenuButton(
                ">> 報酬受け取り画面",
                CreateReceiveRewardMenu);

            debugCommandPresenter.AddButton(
                "レビュー誘導表示",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    InAppReviewWireFrame.RequestStoreReview(null);
                });

            debugCommandPresenter.AddButton(
                "キャラ獲得ダイアログ",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    UnitReceiveWireFrame.ShowReceivedUnit(new MasterDataId("chara_kai_00501"), ViewController);
                });
            debugCommandPresenter.AddButton(
                "Boxガチャ結果ダイアログ",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    
                    var coin = PlayerResourceModelFactory.Create(
                        ResourceType.Coin, 
                        MasterDataId.Empty, 
                        new PlayerResourceAmount(300));
                    var diamond = PlayerResourceModelFactory.Create(
                        ResourceType.FreeDiamond, 
                        MasterDataId.Empty,
                        new PlayerResourceAmount(300));
                    var unit = PlayerResourceModelFactory.Create(
                        ResourceType.Unit,
                        new MasterDataId("chara_aka_00201"),
                        new PlayerResourceAmount(1));
        
                    var coinViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(coin);
                    var diamondViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(diamond);
                    var unitViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(unit);
                    
                    var artwork = ArtworkFragmentAcquisitionModelFactory.CreateArtworkFragmentAcquisitionModel(
                        new List<UserArtworkModel>()
                        {
                            new UserArtworkModel(
                                new MasterDataId("artwork_dan_0002"),
                                NewEncyclopediaFlag.False)  
                        },
                        new MasterDataId("artwork_dan_0002"),
                        new List<UserArtworkFragmentModel>());
                    
                    var artworkViewModel = ArtworkFragmentAcquisitionViewModelTranslator.ToTranslate(
                        new List<ArtworkFragmentAcquisitionModel>()
                        {
                            artwork
                        });
                    
                    var viewModel = new BoxGachaResultViewModel(
                        new List<GachaResultCellViewModel>() 
                        {
                            new GachaResultCellViewModel(coinViewModel with
                            {
                                Amount = new PlayerResourceAmount(100)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(coinViewModel with
                            {
                                Amount = new PlayerResourceAmount(100)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(diamondViewModel with
                            {
                                Amount = new PlayerResourceAmount(10)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(diamondViewModel with
                            {
                                Amount = new PlayerResourceAmount(20)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(coinViewModel with
                            {
                                Amount = new PlayerResourceAmount(200)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(diamondViewModel with
                            {
                                Amount = new PlayerResourceAmount(40)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(diamondViewModel with
                            {
                                Amount = new PlayerResourceAmount(40)
                            }, IsNewUnitBadge.Empty),
                            new GachaResultCellViewModel(unitViewModel, new IsNewUnitBadge(true)),
                        },
                        new List<PlayerResourceIconViewModel>()
                        {
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty,
                            PlayerResourceIconViewModel.Empty
                        },
                        new List<PlayerResourceIconViewModel>()
                        {
                            unitViewModel
                        },
                        PreConversionResourceExistenceFlag.False, 
                        UnreceivedByResourceOverflowDiscardedFlag.False,
                        artworkViewModel);

                    var controller =
                        ViewFactory
                            .Create<BoxGachaResultViewController,
                                BoxGachaResultViewController.Argument>(new BoxGachaResultViewController.Argument(viewModel));
                    ViewController.PresentModally(controller);
                });

            debugCommandPresenter.AddButton(
                "プレイヤーレベルアップ演出(報酬二列)",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    var playerResourceResults = new[]
                    {
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.Coin,
                            Amount = new PlayerResourceAmount(1000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.FreeDiamond,
                            Amount = new PlayerResourceAmount(2000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.Coin,
                            Amount = new PlayerResourceAmount(3000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.FreeDiamond,
                            Amount = new PlayerResourceAmount(2000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.Coin,
                            Amount = new PlayerResourceAmount(3000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.Coin,
                            Amount = new PlayerResourceAmount(3000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceId = MasterDataId.Empty,
                            ResourceType = ResourceType.FreeDiamond,
                            Amount = new PlayerResourceAmount(2000)
                        },
                    };

                    var acquiredPlayerResources = playerResourceResults
                        .Select(r =>
                            PlayerResourceModelFactory.Create(
                                r.ResourceType,
                                r.ResourceId,
                                r.Amount))
                        .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                        .ThenBy(playerResource => playerResource.SortOrder.Value)
                        .ToList();

                    var models =
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(acquiredPlayerResources);

                    var viewModel = new UserLevelUpResultViewModel(
                        new UserLevel(100),
                        models,
                        new Stamina(50),
                        new Stamina(66),
                        false);

                    UserLevelUpResultViewFacade.Show(viewModel);
                });

            debugCommandPresenter.AddButton(
                "MyId表示",
                () =>
                {
                    var profile = UseCases.GetUserProfile();

                    MessageViewUtil.ShowMessageWith2Buttons(
                        "MyId",
                        profile.MyId.Value,
                        string.Empty,
                        "コピー",
                        "閉じる",
                        () => GUIUtility.systemCopyBuffer = profile.MyId.Value,
                        () => { });
                });

            debugCommandPresenter.AddButton(
                "WebView表示",
                () => { CommonWebViewControl.ShowWebView(WebViewShownContentType.FundsSettlement); });
            debugCommandPresenter.AddButton(
                "レイアウト確認用グリッド表示",
                () =>
                {
                    var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);
                    var view = Context.Container.Resolve<DebugGridView>();
                    var controller = new DebugGridViewController()
                    {
                        TempleteView = view
                    };
                    canvas.RootViewController.Show(controller);

                    debugCommandPresenter.CloseMenu();
                });
            debugCommandPresenter.AddButton(
                "プロファイラー表示/非表示",
                () =>
                {
                    var profile = Context.Container.Resolve<DebugSystemUsageProfile>();
                    profile.Enabled = !profile.Enabled;
                });
            debugCommandPresenter.AddButton(
                "ソフトReboot",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    ApplicationRebootor.SoftReboot();
                });
            debugCommandPresenter.AddButton(
                "debug log viewer view",
                () =>
                {
                    var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);
                    var view = Context.Container.Resolve<DebugLogViewerView>();
                    var controller = new DebugLogViewerViewController()
                    {
                        TempleteView = view
                    };
                    canvas.RootViewController.Show(controller);

                    debugCommandPresenter.CloseMenu();
                });

            debugCommandPresenter.AddButton(
                "キャラ詳細ダイアログ",
                () =>
                {
                    var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.First();
                    var argument = new UnitDetailModalViewController.Argument(userUnit.MstUnitId, MaxStatusFlag.True);
                    var controller =
                        ViewFactory.Create<UnitDetailModalViewController, UnitDetailModalViewController.Argument>(
                            argument);
                    ViewController.PresentModally(controller);
                    debugCommandPresenter.CloseMenu();
                });
            debugCommandPresenter.AddButton(
                "ローカル：所持限界",
                () =>
                {
                    var gameFetchModel = GameRepository.GetGameFetch();
                    var param = gameFetchModel.UserParameterModel;
                    var debugUserParamModel = new UserParameterModel(
                        param.Level,
                        param.Exp,
                        new Coin(999999999 + 1),
                        new Stamina(0),
                        TimeProvider.Now,
                        new FreeDiamond(999999999 + 1),
                        new PaidDiamondIos(999999999 + 1),
                        new PaidDiamondAndroid(999999999 + 1),
                        param.UserDailyBuyStamina
                    );

                    GameManagement.SaveGameFetch(gameFetchModel with
                    {
                        UserParameterModel = debugUserParamModel
                    });
                    HomeHeaderDelegate.UpdateStatus();

                    Toast.MakeText("所持限界にしました").Show();
                    debugCommandPresenter.CloseMenu();
                });
            debugCommandPresenter.AddButton(
                "ローカル：所持限界(ランダムかけらBOX N/ねねこのかけら)",
                () =>
                {
                    var gameFetchOtherModel = GameRepository.GetGameFetchOther();
                    var param = gameFetchOtherModel.UserItemModels.ToList();
                    var a = param.FirstOrDefault(p => p.MstItemId.Value == "box_glo_00001");
                    if (a != null)
                    {
                        var debugAddItem = new UserItemModel(
                            a.UsrItemId,
                            new MasterDataId("box_glo_00001"),
                            new ItemAmount(999999999 + 1)
                        );
                        param[param.IndexOf(a)] = debugAddItem;
                    }
                    else
                    {
                        var debugAddItem = new UserItemModel(
                            param.First().UsrItemId,
                            new MasterDataId("box_glo_00001"),
                            new ItemAmount(999999999 + 1)
                        );
                        param.Add(debugAddItem);
                    }

                    var b = param.FirstOrDefault(p => p.MstItemId.Value == "piece_glo_00010");
                    if (b != null)
                    {
                        var debugAddItem = new UserItemModel(
                            b.UsrItemId,
                            new MasterDataId("piece_glo_00010"),
                            new ItemAmount(999999999 + 1)
                        );
                        param[param.IndexOf(b)] = debugAddItem;
                    }
                    else
                    {
                        var debugAddItem = new UserItemModel(
                            param.First().UsrItemId,
                            new MasterDataId("piece_glo_00010"),
                            new ItemAmount(999999999 + 1)
                        );
                        param.Add(debugAddItem);
                    }

                    GameManagement.SaveGameUpdateAndFetch(GameRepository.GetGameFetch(), gameFetchOtherModel with
                    {
                        UserItemModels = param.ToList()
                    });
                    HomeHeaderDelegate.UpdateStatus();

                    Toast.MakeText("ランダムかけらBOX N/\nねねこのかけらを所持限界にしました").Show();
                    debugCommandPresenter.CloseMenu();
                });

            debugCommandPresenter.AddNestedMenuButton(
                ">> client向け実装サンプル",
                CreateClientDevelopSamples);
            debugCommandPresenter.AddNestedMenuButton(
                ">> 触覚フィードバック",
                CreateHapticSelectMenu);
            debugCommandPresenter.AddButton(
                "アセットチェック画面表示",
                () =>
                {
                    var controller = ViewFactory.Create<DebugAssetExistsCheckerViewController>();
                    ViewController.Show(controller);

                    debugCommandPresenter.CloseMenu();
                });
            debugCommandPresenter.AddButton(
                "チュートリアル フリーパート全完了",
                CompleteFreePartTutorial);
            debugCommandPresenter.AddButton(
                "日付変更通知ダイアログ",
                () =>
                {
                    MessageViewUtil.ShowMessageWithOk(
                        "日付変更",
                        "日付が変わりました。\nタイトル画面へ戻ります。",
                        string.Empty,
                        () => { },
                        false);
                });
            debugCommandPresenter.AddButton(
                "PvPローカル通知テスト",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    LocalNotificationScheduler.DebugRefreshSchedule(
                        LocalNotificationType.RemainPvP,
                        "PvPローカル通知テストです",
                        DateTime.Now.AddMinutes(1));
                });
            debugCommandPresenter.AddButton(
                "降臨バトルリザルトチェック1位",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    ShowAdventBattleRankingResultView(new AdventBattleRankingRank(1));
                });
            debugCommandPresenter.AddButton(
                "降臨バトルリザルトチェック2位",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    ShowAdventBattleRankingResultView(new AdventBattleRankingRank(2));
                });
            debugCommandPresenter.AddButton(
                "降臨バトルリザルトチェック3位",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    ShowAdventBattleRankingResultView(new AdventBattleRankingRank(3));
                });
            debugCommandPresenter.AddButton(
                "降臨バトルリザルトチェック4位以降",
                () =>
                {
                    debugCommandPresenter.CloseMenu();

                    ShowAdventBattleRankingResultView(new AdventBattleRankingRank(20));
                });
            debugCommandPresenter.AddButton(
                "降臨バトル協力リザルトチェック",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    var viewModel = new AdventBattleRankingResultViewModel(
                        RankType.Bronze,
                        new AdventBattleScoreRankLevel(2),
                        new AdventBattleRankingRank(3),
                        new AdventBattleScore(15),
                        new List<PlayerResourceIconViewModel>(),
                        AdventBattleType.Raid,
                        new UnitImageAssetPath("unit_enemy_dan_00101"),
                        new AdventBattleName("降臨バトル"));

                    var argument = new AdventBattleRaidRankingResultViewController.Argument(viewModel, () => { });

                    var adventBattleResultViewController =
                        ViewFactory
                            .Create<AdventBattleRaidRankingResultViewController,
                                AdventBattleRaidRankingResultViewController.Argument>(argument);
                    ViewController.PresentModally(adventBattleResultViewController);
                });

            debugCommandPresenter.AddButton(
                "クライアントエラー発生",
                () => throw new InvalidOperationException("これはデバッグ用のクライアントエラーです")
            );
        }

        void StartStageForDebug(IDebugCommandPresenter debugCommandPresenter, MasterDataId mstStageId)
        {
            DisableInGameApiCallDebugUseCase.DisableApiCall();
            SelectStageUseCase.SelectStage(mstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty);

            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();

            debugCommandPresenter.CloseMenu();
        }

        void StartPvpForDebug(IDebugCommandPresenter debugCommandPresenter)
        {
            DisableInGameApiCallDebugUseCase.DisableApiCall();
            SelectStageUseCase.SelectStage(MasterDataId.Empty, MasterDataId.Empty, PvpConst.DefaultSysPvpSeasonId);

            // PVPの対戦相手を仮で設定
            PvpSelectedOpponentStatusCacheRepository.SetOpponentStatus(
                OpponentPvpStatusModel.Empty with
                {
                    PvpUnits = new List<PvpUnitModel>()
                    {
                        PvpUnitModel.Empty with
                        {
                            // 山田浅ェ門 仙汰
                            MstUnitId = new MasterDataId("chara_jig_00301"),
                            UnitLevel = new UnitLevel(7),
                            UnitRank = new UnitRank(2),
                            UnitGrade = new UnitGrade(1),
                        },
                        PvpUnitModel.Empty with
                        {
                            // オカルン
                            MstUnitId = new MasterDataId("chara_dan_00001"),
                            UnitLevel = new UnitLevel(10),
                            UnitRank = new UnitRank(2),
                            UnitGrade = new UnitGrade(2),
                        },
                        PvpUnitModel.Empty with
                        {
                            // アーニャ（スペシャル）
                            MstUnitId = new MasterDataId("chara_spy_00001"),
                            UnitLevel = new UnitLevel(5),
                            UnitRank = new UnitRank(1),
                            UnitGrade = new UnitGrade(1),
                        },
                        PvpUnitModel.Empty with
                        {
                            // 画眉丸
                            MstUnitId = new MasterDataId("chara_jig_00001"),
                            UnitLevel = new UnitLevel(5),
                            UnitRank = new UnitRank(1),
                            UnitGrade = new UnitGrade(2),
                        },
                        PvpUnitModel.Empty with
                        {
                            // 東 日万凛
                            MstUnitId = new MasterDataId("chara_sur_00201"),
                            UnitLevel = new UnitLevel(3),
                            UnitRank = new UnitRank(2),
                            UnitGrade = new UnitGrade(2),
                        },
                        PvpUnitModel.Empty with
                        {
                            // 寧
                            MstUnitId = new MasterDataId("chara_sum_00201"),
                            UnitLevel = new UnitLevel(6),
                            UnitRank = new UnitRank(2),
                            UnitGrade = new UnitGrade(2),
                        },
                        PvpUnitModel.Empty with
                        {
                            // 文蔵
                            MstUnitId = new MasterDataId("chara_aka_00101"),
                            UnitLevel = new UnitLevel(5),
                            UnitRank = new UnitRank(1),
                            UnitGrade = new UnitGrade(1),
                        },
                        PvpUnitModel.Empty with
                        {
                            // トーチャー・トルチュール
                            MstUnitId = new MasterDataId("chara_gom_00101"),
                            UnitLevel = new UnitLevel(8),
                            UnitRank = new UnitRank(1),
                            UnitGrade = new UnitGrade(1),
                        },
                    },
                    UsrOutpostEnhancements = new List<UserOutpostEnhanceModel>()
                    {
                        UserOutpostEnhanceModel.Empty with
                        {
                            // Vビーム攻撃力
                            MstOutpostId = new MasterDataId("outpost_1"),
                            MstOutpostEnhanceId = new MasterDataId("enhance_1_1"),
                            Level = new OutpostEnhanceLevel(3),
                        },
                        UserOutpostEnhanceModel.Empty with
                        {
                            // リーダーPの増加スピードアップ
                            MstOutpostId = new MasterDataId("outpost_1"),
                            MstOutpostEnhanceId = new MasterDataId("enhance_1_3"),
                            Level = new OutpostEnhanceLevel(2),
                        },
                    },
                    UsrEncyclopediaEffects = new List<PvpEncyclopediaEffectModel>()
                    {
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_5")
                        },
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_10")
                        },
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_15")
                        },
                    }
                });

            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();

            debugCommandPresenter.CloseMenu();
        }

        void StartPvpVsMyPartyForDebug(IDebugCommandPresenter debugCommandPresenter)
        {
            DisableInGameApiCallDebugUseCase.DisableApiCall();
            SelectStageUseCase.SelectStage(MasterDataId.Empty, MasterDataId.Empty, PvpConst.DefaultSysPvpSeasonId);

            DebugSetMyPartyToPvpOpponentUseCase.SetUpPvpVsMyParty();

            SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();

            debugCommandPresenter.CloseMenu();
        }

        void CreateDebugStageSelectMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            var nameFormat = "{0} : {1} {2}話";
            var stages = GetAllStagesDebugUseCase.GetAllStages();

            foreach (var stage in stages)
            {
                debugCommandPresenter.AddButton(
                    ZString.Format(
                        nameFormat,
                        stage.Difficulty,
                        stage.MstStageModel.Name.Value,
                        stage.MstStageModel.StageNumber.Value),
                    () => StartStageForDebug(debugCommandPresenter, stage.MstStageModel.Id));
            }
        }

        void CreateUnitCheckSelectMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            var format = "{0} : {1}";
            var models = DebugMstUnitStatusUseCase.GetModels();

            var sorted = models
                .OrderBy(m => m.AttackStatus.AtBase.SeriesPrefixWord.Value)
                .ThenBy(m => m.AttackStatus.AtBase.SeriesName.Value);
            foreach (var useCaseModel in sorted)
            {
                debugCommandPresenter.AddButton(
                    ZString.Format(
                        format,
                        useCaseModel.AttackStatus.AtBase.SeriesName.Value,
                        useCaseModel.AttackStatus.AtBase.CharacterName.ToString()),
                    () =>ShowUnitStatusView(useCaseModel));
            }
        }

        void ShowUnitStatusView(DebugMstUnitStatusUseCaseModel useCaseModel)
        {
            var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);

            var controller = ViewFactory.Create<DebugMstUnitStatusViewController>();
            controller.SetUp(useCaseModel);
            canvas.RootViewController.Show(controller);
        }

        void CreateStageCheckSelectMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            var models = DebugStageSummaryUseCase.GetModels();
            foreach (var model in models)
            {
                debugCommandPresenter.AddButton(
                    model.NameString(),
                    () => ShowStageDetailView(model));

            }
        }

        void ShowStageDetailView(DebugStageSummaryUseCaseModel useCaseModel)
        {
            var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);

            var controller = ViewFactory.Create<DebugStageDetailViewController>();
            controller.SetUp(useCaseModel);
            canvas.RootViewController.Show(controller);
        }


        void CreateReceiveRewardMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "報酬受け取り画面",
                () =>
                {
                    // 報酬
                    var playerResourceResults = new[]
                    {
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00001"),
                            Amount = new PlayerResourceAmount(1)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00002"),
                            Amount = new PlayerResourceAmount(1)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00003"),
                            Amount = new PlayerResourceAmount(1)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00004"),
                            Amount = new PlayerResourceAmount(1)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00005"),
                            Amount = new PlayerResourceAmount(1)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00001"),
                            Amount = new PlayerResourceAmount(2)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00002"),
                            Amount = new PlayerResourceAmount(3)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00003"),
                            Amount = new PlayerResourceAmount(4)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00004"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00006"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00007"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("box_glo_00008"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00001"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00101"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00201"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00301"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00401"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_spy_00501"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_aka_00001"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_aka_00101"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_rik_00001"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_dan_00001"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_dan_00002"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_dan_00101"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_dan_00201"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_dan_00301"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_gom_00001"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_gom_00101"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("piece_gom_00201"),
                            Amount = new PlayerResourceAmount(5)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Exp,
                            ResourceId = MasterDataId.Empty,
                            Amount = new PlayerResourceAmount(1000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.FreeDiamond,
                            ResourceId = MasterDataId.Empty,
                            Amount = new PlayerResourceAmount(2000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Coin,
                            ResourceId = MasterDataId.Empty,
                            Amount = new PlayerResourceAmount(3000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Exp,
                            ResourceId = MasterDataId.Empty,
                            Amount = new PlayerResourceAmount(1000)
                        },
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Coin,
                            ResourceId = MasterDataId.Empty,
                            Amount = new PlayerResourceAmount(3000)
                        }
                    };

                    var models = playerResourceResults
                        .Select(r =>
                            new CommonReceiveResourceModel(
                                UnreceivedRewardReasonType.None,
                                PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                                PlayerResourceModelFactory.Create(r.PreConversionResource))
                        )
                        .OrderBy(playerResource => playerResource.PlayerResourceModel.GroupSortOrder.Value)
                        .ThenBy(playerResource => playerResource.PlayerResourceModel.SortOrder.Value)
                        .ToList();

                    var viewModels = models
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    CommonReceiveWireFrame.Show(viewModels);
                });
            debugCommandPresenter.AddButton(
                "報酬受け取り画面(所持上限)",
                () =>
                {
                    // 報酬
                    var playerResourceResults = new[]
                    {
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00001"),
                            Amount = new PlayerResourceAmount(1)
                        },
                    };

                    var models = playerResourceResults
                        .Select(r =>
                            new CommonReceiveResourceModel(
                                UnreceivedRewardReasonType.ResourceLimitReached,
                                PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                                PlayerResourceModelFactory.Create(r.PreConversionResource))
                        )
                        .OrderBy(playerResource => playerResource.PlayerResourceModel.GroupSortOrder.Value)
                        .ThenBy(playerResource => playerResource.PlayerResourceModel.SortOrder.Value)
                        .ToList();

                    var viewModels = models
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    CommonReceiveWireFrame.Show(viewModels);
                });
            debugCommandPresenter.AddButton(
                "報酬受け取り画面(メールBOX送信)",
                () =>
                {
                    // 報酬
                    var playerResourceResults = new[]
                    {
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00001"),
                            Amount = new PlayerResourceAmount(1)
                        },
                    };

                    var models = playerResourceResults
                        .Select(r =>
                            new CommonReceiveResourceModel(
                                UnreceivedRewardReasonType.SentToMessage,
                                PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                                PlayerResourceModelFactory.Create(r.PreConversionResource))
                        )
                        .OrderBy(playerResource => playerResource.PlayerResourceModel.GroupSortOrder.Value)
                        .ThenBy(playerResource => playerResource.PlayerResourceModel.SortOrder.Value)
                        .ToList();

                    var viewModels = models
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    CommonReceiveWireFrame.Show(viewModels);
                });
            debugCommandPresenter.AddButton(
                "報酬受け取り画面(取得アイテム破棄)",
                () =>
                {
                    // 報酬
                    var playerResourceResults = new[]
                    {
                        RewardModel.Empty with
                        {
                            ResourceType = ResourceType.Item,
                            ResourceId = new MasterDataId("memory_glo_00001"),
                            Amount = new PlayerResourceAmount(1)
                        },
                    };

                    var models = playerResourceResults
                        .Select(r =>
                            new CommonReceiveResourceModel(
                                UnreceivedRewardReasonType.ResourceOverflowDiscarded,
                                PlayerResourceModelFactory.Create(r.ResourceType, r.ResourceId, r.Amount),
                                PlayerResourceModelFactory.Create(r.PreConversionResource))
                        )
                        .OrderBy(playerResource => playerResource.PlayerResourceModel.GroupSortOrder.Value)
                        .ThenBy(playerResource => playerResource.PlayerResourceModel.SortOrder.Value)
                        .ToList();

                    var viewModels = models
                        .Select(m => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(m))
                        .ToList();
                    CommonReceiveWireFrame.Show(viewModels);
                });
        }

        void CreateFakeStoreSelectMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "Default: すべて成功",
                () =>
                {
                    PreferenceRepository.InAppPurchaseFakeStoreMode = InAppPurchaseFakeStoreMode.Default;
                    OnBackTitle();
                }
            );
            debugCommandPresenter.AddButton(
                "StandardUser: 購入時にBuyかCancelかが選べる",
                () =>
                {
                    PreferenceRepository.InAppPurchaseFakeStoreMode = InAppPurchaseFakeStoreMode.StandardUser;
                    OnBackTitle();
                }
            );
            debugCommandPresenter.AddButton(
                "DeveloperUser: 初期化と購入時に各種エラー or Success(成功)かを選べる",
                () =>
                {
                    PreferenceRepository.InAppPurchaseFakeStoreMode = InAppPurchaseFakeStoreMode.DeveloperUser;
                    OnBackTitle();
                }
            );
        }

        void CreateClientDevelopSamples(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "試作：UICanvas.Canvases情報Debug.Log",
                () =>
                {
                    foreach (var b in UICanvas.Canvases)
                    {
                        Debug.Log("a.name: " + b.name + " / root: " + b.RootViewController.View.name + " / type: " +
                                  b.RootViewController.GetType());
                    }
                });
            debugCommandPresenter.AddButton(
                "試作：モーダル全非表示",
                () =>
                {
                    var a = UICanvas.Canvases
                        .Select(c => c.RootViewController)
                        .Where(vc => vc is WPFramework.Presentation.Views.ModalItemHostingController)
                        .Where(vc => 1 <= vc.ChildViewControllers.Count)
                        .Select(vc => vc.ChildViewControllers.First())
                        .ToList();
                    foreach (var b in a)
                    {
                        b.Dismiss();
                    }
                });
        }

        void CreateHapticSelectMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            debugCommandPresenter.AddButton(
                "Haptic Light",
                () => { HapticsPresenter.Impact(UIImpactFeedback.Style.Light); });
            debugCommandPresenter.AddButton(
                "Haptic Medium",
                () => { HapticsPresenter.Impact(UIImpactFeedback.Style.Medium); });
            debugCommandPresenter.AddButton(
                "Haptic Heavy",
                () => { HapticsPresenter.Impact(UIImpactFeedback.Style.Heavy); });
            debugCommandPresenter.AddButton(
                "Haptic Peek",
                () => { HapticsPresenter.Impact(UIImpactFeedback.Style.Peek); });
            debugCommandPresenter.AddButton(
                "Haptic Pop",
                () => { HapticsPresenter.Impact(UIImpactFeedback.Style.Pop); });
        }

        void ShowAdventBattleRankingResultView(AdventBattleRankingRank rank)
        {
            var playerResourceResults = new[]
            {
                RewardModel.Empty with
                {
                    ResourceId = MasterDataId.Empty,
                    ResourceType = ResourceType.Exp,
                    Amount = new PlayerResourceAmount(1000)
                },
                RewardModel.Empty with
                {
                    ResourceId = MasterDataId.Empty,
                    ResourceType = ResourceType.FreeDiamond,
                    Amount = new PlayerResourceAmount(2000)
                },
                RewardModel.Empty with
                {
                    ResourceId = MasterDataId.Empty,
                    ResourceType = ResourceType.Coin,
                    Amount = new PlayerResourceAmount(3000)
                },
                RewardModel.Empty with
                {
                    ResourceId = MasterDataId.Empty,
                    ResourceType = ResourceType.FreeDiamond,
                    Amount = new PlayerResourceAmount(2000)
                },
                RewardModel.Empty with
                {
                    ResourceId = MasterDataId.Empty,
                    ResourceType = ResourceType.Coin,
                    Amount = new PlayerResourceAmount(3000)
                }
            };

            var acquiredPlayerResources = playerResourceResults
                .Select(r =>
                    PlayerResourceModelFactory.Create(
                        r.ResourceType,
                        r.ResourceId,
                        r.Amount))
                .OrderBy(playerResource => playerResource.GroupSortOrder.Value)
                .ThenBy(playerResource => playerResource.SortOrder.Value)
                .ToList();

            var models =
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(acquiredPlayerResources);

            var viewModel = new AdventBattleRankingResultViewModel(
                RankType.Master,
                new AdventBattleScoreRankLevel(2),
                rank,
                new AdventBattleScore(1500000),
                models,
                AdventBattleType.ScoreChallenge,
                new UnitImageAssetPath("unit_enemy_dan_00101"),
                new AdventBattleName("第XX回 降臨バトル"));

            var argument = new AdventBattleRankingResultViewController.Argument(viewModel, () => { });

            var adventBattleResultViewController =
                ViewFactory
                    .Create<AdventBattleRankingResultViewController,
                        AdventBattleRankingResultViewController.Argument>(argument);
            ViewController.PresentModally(adventBattleResultViewController);
        }

        void CompleteFreePartTutorial()
        {
            var cancellationToken = new CancellationTokenSource().Token;
            DoAsync.Invoke(cancellationToken, ScreenInteractionControl,async ct =>
            {
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ReleaseEventQuest);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ReleaseAdventBattle);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ReleaseHardStage);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ReleasePvp);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ReleaseEnhanceQuest);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.OutpostEnhance);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.SpecialUnit);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.IdleIncentive);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.ArtworkFragment);
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(ct, TutorialFreePartIdDefinitions.TransitPvp);
            });
        }
    }
}
#endif
