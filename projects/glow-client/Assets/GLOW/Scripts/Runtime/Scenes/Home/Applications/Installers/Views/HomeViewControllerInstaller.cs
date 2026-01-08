using GLOW.Core.Constants.Zenject;
using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Services;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Loader;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Application.Installers.Views;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.Views;
using GLOW.Modules.CommonWebView.Application.Installers;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.CommonWebView.Presentation.View;
using GLOW.Modules.InAppReview.Presentation;
using GLOW.Modules.Tutorial.Application.Context;
using GLOW.Modules.Tutorial.Application.Installers;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Presenters;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using GLOW.Modules.TutorialTipDialog.Domain.UseCase;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.AdventBattle.Application;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo;
using GLOW.Scenes.AdventBattleMission.Application.View;
using GLOW.Scenes.AdventBattleMission.Domain.Evaluator;
using GLOW.Scenes.AdventBattleMission.Presentation.View;
using GLOW.Scenes.AdventBattleRaidRankingResult.Application;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRanking.Application;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Application;
using GLOW.Scenes.AdventBattleRankingResult.Domain.ModelFactories;
using GLOW.Scenes.AdventBattleRankingResult.Domain.UseCases;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRewardList.Application.Installers.Views;
using GLOW.Scenes.AdventBattleRewardList.Presentation.View;
using GLOW.Scenes.AgeConfirm.Application.Views;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using GLOW.Scenes.AnnouncementWindow.Application.Installers;
using GLOW.Scenes.AnnouncementWindow.Domain.Applier;
using GLOW.Scenes.AnnouncementWindow.Domain.Evaluator;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using GLOW.Scenes.AppAppliedBalanceDialog.Application;
using GLOW.Scenes.AppAppliedBalanceDialog.Presentation;
using GLOW.Scenes.ArtworkExpandDialog.Application.Views;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.ArtworkFragmentAcquisition.Application.Installers;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.BattleResult.Application.Installers.Views;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.BeginnerMission.Application.Installers;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.ComebackDailyBonus.Application.Installers;
using GLOW.Scenes.ComebackDailyBonus.Presentation.View;
using GLOW.Scenes.DiamondConsumeConfirm.Application.Views;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.Views;
using GLOW.Scenes.DiamondPurchaseHistory.Application;
using GLOW.Scenes.DiamondPurchaseHistory.Presentation;
using GLOW.Scenes.EmblemDetail.Application;
using GLOW.Scenes.EmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Application.Views;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaReward.Application.Views;
using GLOW.Scenes.EncyclopediaReward.Presentation.Views;
using GLOW.Scenes.EncyclopediaTop.Application.Views;
using GLOW.Scenes.EncyclopediaTop.Presentation.Views;
using GLOW.Scenes.EnhanceQuestTop.Application.Views;
using GLOW.Scenes.EnhanceQuestTop.Presentation.Views;
using GLOW.Scenes.EventBonusUnitList.Application.Views;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using GLOW.Scenes.EventMission.Application.Installers.View;
using GLOW.Scenes.EventMission.Domain.UseCase;
using GLOW.Scenes.EventMission.Presentation.Facade;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.EventQuestSelect.Application;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.EventQuestTop.Application;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.EventQuestTop.Presentation.Views;
using GLOW.Scenes.ExchangeShop.Application.Installer.View;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Presenter;
using GLOW.Scenes.ExchangeShop.Domain.Provider;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.FragmentProvisionRatio.Application;
using GLOW.Scenes.FragmentProvisionRatio.Presentation;
using GLOW.Scenes.GachaCostItemDetailView.Application.Views;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Application.Views;
using GLOW.Scenes.GachaDetailDialog.Presentation.Views;
using GLOW.Scenes.GachaLineupDialog.Application.Views;
using GLOW.Scenes.GachaLineupDialog.Presentation.Views;
using GLOW.Scenes.GachaList.Application.Views;
using GLOW.Scenes.GachaList.Domain.Applier;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.GachaRatio.Application.Views;
using GLOW.Scenes.GachaRatio.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Modules.Systems;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView;
using GLOW.Scenes.HomeMenu.Application.Views;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.HomePartyFormation.Application.Views;
using GLOW.Scenes.HomePartyFormation.Domain.UseCases;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.IdleIncentiveQuickReward.Application.Views;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Application.Views;
using GLOW.Scenes.IdleIncentiveTop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGameSpecialRule.Application;
using GLOW.Scenes.InGameSpecialRule.Domain.Evaluator;
using GLOW.Scenes.InGameSpecialRule.Domain.ModelFactories;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.ItemBox.Application.Views;
using GLOW.Scenes.ItemBox.Presentation.Presenters;
using GLOW.Scenes.ItemBox.Presentation.Views;
using GLOW.Scenes.ItemDetail.Domain.Factory;
using GLOW.Scenes.ItemDetail.Domain.UseCase;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.MessageBox.Application.Installers;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.Mission.Application.Installers;
using GLOW.Scenes.Mission.Domain.Creator;
using GLOW.Scenes.Mission.Domain.UseCase;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using GLOW.Scenes.Notice.Application.Installers;
using GLOW.Scenes.Notice.Domain.Factory;
using GLOW.Scenes.Notice.Domain.Initializer;
using GLOW.Scenes.Notice.Presentation.Facade;
using GLOW.Scenes.Notice.Presentation.View;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PvpBattleFinishAnimation.Application.View;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using GLOW.Scenes.PvpRewardList.Application.View;
using GLOW.Scenes.PvpRewardList.Presentation.View;
using GLOW.Scenes.PvpInfo.Application;
using GLOW.Scenes.PvpInfo.Presentation.View;
using GLOW.Scenes.PvpBattleResult.Application.View;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using GLOW.Scenes.PvpNewSeasonStart.Application.Installers;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.Views;
using GLOW.Scenes.PvpOpponentDetail.Application;
using GLOW.Scenes.PvpOpponentDetail.Presentation.Views;
using GLOW.Scenes.PvpPreviousSeasonResult.Application.Installers;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views;
using GLOW.Scenes.PvpRanking.Application;
using GLOW.Scenes.PvpRanking.Presentation.Views;
using GLOW.Scenes.PvpTop.Application;
using GLOW.Scenes.PvpTop.Domain;
using GLOW.Scenes.PvpTop.Domain.UseCase;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm;
using GLOW.Scenes.QuestContentTop.Application;
using GLOW.Scenes.QuestContentTop.Domain;
using GLOW.Scenes.QuestContentTop.Presentation;
using GLOW.Scenes.QuestContentTop.Presentation.WireFrame;
using GLOW.Scenes.QuestSelect.Applications;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.SelectFragmentItemBoxTransit.Application;
using GLOW.Scenes.SelectFragmentItemBoxTransit.Presentation;
using GLOW.Scenes.Shop.Domain.Calculator;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.ItemBox.Domain.Evaluator;
using GLOW.Scenes.LinkBnIdDialog.Application.Views;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.Notice.Domain.Evaluator;
using GLOW.Scenes.PackShop.Domain.Calculator;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.ShopBuyConform.Application.Installers.View;
using GLOW.Scenes.ShopBuyConform.Presentation.View;
using GLOW.Scenes.ShopTab.Application.Installer.View;
using GLOW.Scenes.ShopTab.Domain.Factory;
using GLOW.Scenes.ShopTab.Domain.UseCase;
using GLOW.Scenes.ShopTab.Presentation.View;
using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.StaminaBoostDialog.Application.Installer;
using GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator;
using GLOW.Scenes.StaminaBoostDialog.Presentation.View;
using GLOW.Scenes.StaminaRecover.Application;
using GLOW.Scenes.StaminaRecover.Domain;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.TradeShop.Presentation.View;
using GLOW.Scenes.TutorialGachaReDrawDialog.Application.Installers;
using GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Views;
using GLOW.Scenes.UnitDetail.Application.Views;
using GLOW.Scenes.UnitDetail.Presentation.Views;
using GLOW.Scenes.UnitDetailModal.Application.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitReceive.Application.View;
using GLOW.Scenes.UnitReceive.Domain.UseCase;
using GLOW.Scenes.UnitReceive.Presentation.View;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Translators;
using GLOW.Scenes.UnitTab.Application.Views;
using GLOW.Scenes.UnitTab.Domain.UseCase;
using GLOW.Scenes.UnitTab.Presentation.Views;
using GLOW.Scenes.UserEmblem.Application.Views;
using GLOW.Scenes.UserEmblem.Presentation.Views;
using GLOW.Scenes.UserLevelUp.Application.Installer.View;
using GLOW.Scenes.UserLevelUp.Domain.UseCase;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using GLOW.Scenes.UserProfile.Application.Views;
using GLOW.Scenes.UserProfile.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using WPFramework.Data.Repositories;
using WPFramework.Domain.Repositories;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Home.Presentation.DebugCommands;
#endif

namespace GLOW.Scenes.Home.Applications.Installers.Views
{
    public class HomeViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeViewController>();
            Container.BindInterfacesTo<HomePresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();

            InstallUtil();
            InstallHome();
            InstallStageStart();
            InstallNavigator();
            InstallHelpers();
            InstallDebug();
            InstallSelectFragmentItemBox();
            InstallGacha();
            InstallTutorial();
            InstallInAppPurchase();
            InstallEvent();
            InstallPvp();
            InstallTradeShop();

            Container.BindInterfacesTo<LimitAmountModelCalculator>().AsCached();
            Container.BindInterfacesTo<LimitAmountWireframe>().AsCached();

            Container.BindInterfacesTo<ItemDetailWireFrame>().AsCached();
            Container.Bind<ShowItemDetailUseCase>().AsCached();
            Container.Bind<ShowArtworkDetailUseCase>().AsCached();
            Container.BindInterfacesTo<ItemDetailAmountModelFactory>().AsCached();
            Container.BindInterfacesTo<ItemDetailAvailableLocationModelFactory>().AsCached();
            Container.BindViewFactoryInfo<GachaCostItemDetailViewController, GachaCostItemDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                EmblemDetailViewController,
                EmblemDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<AppAppliedBalanceDialogViewController, AppAppliedBalanceDialogViewInstaller>();

            InstallHomeMain();
            Container.BindViewFactoryInfo<RandomFragmentBoxViewController, RandomFragmentBoxViewControllerInstaller>();
            Container.BindViewFactoryInfo<ShopTabViewController, ShopTabViewControllerInstaller>();
            Container.BindViewFactoryInfo<IdleIncentiveTopViewController, IdleIncentiveTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                IdleIncentiveQuickReceiveWindowViewController,
                IdleIncentiveQuickReceiveWindowViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitTabViewController, UnitTabViewControllerInstaller>();
            Container.BindViewFactoryInfo<HomePartyFormationViewController, HomePartyFormationViewControllerInstaller>();
            Container.BindViewFactoryInfo<FragmentProvisionRatioViewController, FragmentProvisionRatioViewInstaller>();

            Container.Bind<HomeStageInfoUseCases>().AsCached();
            Container.Bind<HomeStageInfoViewModelFactory>().AsCached();
            Container.BindViewFactoryInfo<HomeStageInfoViewController, HomeStageInfoViewControllerInstaller>();
            Container.BindInterfacesTo<UnitAttackViewInfoSetLoader>().AsCached();
            Container.BindInterfacesTo<GachaAnimationUnitInfoLoader>().AsCached();
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();
            Container.BindInterfacesTo<StaminaBoostEvaluator>().AsCached();
            Container.BindViewFactoryInfo<StaminaBoostDialogViewController, StaminaBoostDialogViewControllerInstaller>();

            Container.BindInterfacesTo<InGameSpecialRuleModelFactory>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleEvaluator>().AsCached();
            Container.BindInterfacesTo<InGamePartySpecialRuleEvaluator>().AsCached();

            Container.Bind<CommonReceiveWireFrame>().AsCached();
            Container.BindViewFactoryInfo<CommonReceiveViewController, CommonReceiveViewControllerInstaller>();
            Container.BindViewFactoryInfo<AsyncCommonReceiveViewController, AsyncCommonReceiveViewControllerInstaller>();

            Container.Bind<DisplayReceivedUnitUseCase>().AsCached();
            Container.BindInterfacesTo<UnitReceiveWireFrame>().AsCached();
            Container.BindViewFactoryInfo<UnitReceiveViewController, UnitReceiveViewControllerInstaller>();

            Container.Bind<ShowUserLevelUpInfoUseCase>().AsCached();
            Container.BindInterfacesTo<UserLevelUpResultViewFacade>().AsCached();
            Container.BindViewFactoryInfo<UserLevelUpViewController, UserLevelUpResultViewControllerInstaller>();

            Container.BindInterfacesTo<CommonWebViewControl>().AsCached();
            Container.BindViewFactoryInfo<CommonWebViewController, CommonWebViewControllerInstaller>();

            Container.BindViewFactoryInfo<QuestSelectViewController, QuestSelectViewInstaller>();
            Container.BindViewFactoryInfo<HomeStageLimitStatusViewController, HomeStageLimitStatusViewInstaller>();

            Container.BindViewFactoryInfo<StaminaRecoverySelectViewController, StaminaRecoverySelectViewControllerInstaller>();
            Container.BindViewFactoryInfo<StaminaDiamondRecoverConfirmViewController,StaminaDiamondRecoverConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<StaminaTradeViewController, StaminaTradeViewControllerInstaller>();
            Container.BindViewFactoryInfo<StaminaRecoverSelectViewController, StaminaRecoverViewInstaller>();
            Container.BindViewFactoryInfo<DiamondPurchaseViewController, DiamondPurchaseViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondPurchaseHistoryViewController, DiamondPurchaseHistoryViewInstaller>();

            Container.BindViewFactoryInfo<QuestReleaseViewController, QuestReleaseViewInstaller>();
            Container.BindViewFactoryInfo<UnitDetailViewController, UnitDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                EncyclopediaArtworkDetailViewController,
                EncyclopediaArtworkDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitDetailModalViewController, UnitDetailModalViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondConsumeConfirmViewController, DiamondConsumeConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaTopViewController, EncyclopediaTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<EncyclopediaRewardViewController, EncyclopediaRewardViewControllerInstaller>();

            Container.BindInterfacesTo<AnnouncementViewFacade>().AsCached();
            Container.BindViewFactoryInfo<AnnouncementMainViewController, AnnouncementMainViewControllerInstaller>();
            Container.BindViewFactoryInfo<UserProfileViewController, UserProfileViewControllerInstaller>();
            Container.BindViewFactoryInfo<UserEmblemViewController, UserEmblemViewControllerInstaller>();
            Container.BindViewFactoryInfo<HomeMenuViewController, HomeMenuViewControllerInstaller>();
            Container.BindViewFactoryInfo<LinkBnIdDialogViewController, LinkBnIdDialogViewControllerInstaller>();

            Container.BindInterfacesTo<NoticeViewFacade>().AsCached();
            Container.BindViewFactoryInfo<NoticeSimpleBannerViewController, NoticeSimpleBannerViewControllerInstaller>();
            Container.BindViewFactoryInfo<NoticeDialogViewController, NoticeDialogViewControllerInstaller>();
            Container.BindInterfacesTo<DisplayedInGameNoticeRecordResetter>().AsCached();

            Container.BindInterfacesTo<EventMissionWireFrame>().AsCached();
            Container.BindViewFactoryInfo<EventMissionMainViewController, EventMissionMainViewControllerInstaller>();

            Container.BindViewFactoryInfo<MissionMainViewController, MissionMainViewControllerInstaller>();
            Container.BindViewFactoryInfo<BeginnerMissionMainViewController, BeginnerMissionMainViewControllerInstaller>();
            Container.BindViewFactoryInfo<MessageBoxViewController, MessageBoxViewControllerInstaller>();

            Container.BindViewFactoryInfo<ComebackDailyBonusViewController, ComebackDailyBonusViewControllerInstaller>();

            Container.BindInterfacesTo<MessageService>().AsCached();
            Container.BindInterfacesTo<MessageCacheRepository>().AsCached();
            Container.Bind<BulkOpenMessageUseCase>().AsCached();
            Container.BindInterfacesTo<ReceivedDailyBonusRewardLoader>().AsCached();

            Container.BindViewFactoryInfo<InGameSpecialRuleViewController, InGameSpecialRuleViewInstaller>();

            Container.BindViewFactoryInfo<QuestContentTopViewController, QuestContentTopViewInstaller>();

            Container.BindInterfacesTo<AdventBattleRankingModelFactory>().AsCached();
            Container.Bind<AdventBattleWireFrame>().AsCached();

            Container.Bind<EventQuestWireFrame>().AsCached();
            InstallAdventBattle();

            Container.BindViewFactoryInfo<EnhanceQuestTopViewController, EnhanceQuestTopViewControllerInstaller>();

            Container.Bind<ApplyPartyFormationUseCase>().AsCached();
            Container.Bind<ApplyOutpostArtworkUseCase>().AsCached();
            Container.BindInterfacesTo<OutpostService>().AsCached();
            Container.BindInterfacesTo<PartyService>().AsCached();
            Container.BindInterfacesTo<MissionService>().AsCached();
            Container.BindInterfacesTo<UnitStatusCalculator>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusProvider>().AsCached();
            Container.BindInterfacesTo<InGameUnitStatusCalculator>().AsCached();
            Container.BindInterfacesTo<BuffStatePercentageConverter>().AsCached();
            Container.BindInterfacesTo<StateEffectChecker>().AsCached();
            Container.BindInterfacesTo<RandomProvider>().AsCached();
            Container.BindInterfacesTo<InGameEventBonusUnitEffectProvider>().AsCached();
            Container.Bind<TotalPartyStatusCalculator>().AsCached();

            Container.Bind<IInGameUnitEncyclopediaEffectProvider>()
                .WithId(InGameUnitEncyclopediaBindIds.Player)
                .To<InGameUnitEncyclopediaEffectProvider>().AsCached();
            Container.Bind<IInGameUnitEncyclopediaEffectProvider>()
                .WithId(InGameUnitEncyclopediaBindIds.PvpOpponent)
                .To<InGamePvpOpponentUnitEncyclopediaEffectProvider>().AsCached();

            Container.BindInterfacesTo<ShowPassShopProductFactory>().AsCached();
            Container.Bind<GetShopProductNoticeUseCase>().AsCached();
            Container.Bind<GetPackProductNoticeUseCase>().AsCached();
            Container.Bind<GetPassProductNoticeUseCase>().AsCached();
            Container.Bind<InitializeNewShopProductIdUseCase>().AsCached();
            Container.BindInterfacesTo<ShopProductModelCalculator>().AsCached();
            Container.BindInterfacesTo<PackShopProductEvaluator>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveRewardEvaluator>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveRewardAmountCalculator>().AsCached();
            Container.Bind<GetUnitNoticeUseCase>().AsCached();
            Container.Bind<GetContentNoticeUseCase>().AsCached();
            Container.BindInterfacesTo<PvpChallengeStatusFactory>().AsCached();
            Container.Bind<GetOutpostNoticeUseCase>().AsCached();
            Container.Bind<GetEncyclopediaNoticeUseCase>().AsCached();
            Container.Bind<DisplayAtLoginUseCase>().AsCached();
            Container.Bind<CheckExistLoginBonusUseCase>().AsCached();
            Container.Bind<CheckExistComebackDailyBonusUseCase>().AsCached();
            Container.Bind<GetEventNotificationUseCase>().AsCached();
            Container.Bind<GetLatestEventUseCase>().AsCached();
            Container.BindInterfacesTo<BeginnerMissionFinishedEvaluator>().AsCached();
            Container.BindInterfacesTo<HomeMainBadgeFactory>().AsCached();
            Container.BindInterfacesTo<MissionResultModelFactory>().AsCached();
            Container.BindInterfacesTo<DisplayNoticeListFactory>().AsCached();
            Container.BindInterfacesTo<InGameNoticeModelFactory>().AsCached();
            Container.BindInterfacesTo<NoticePassPurchasedEvaluator>().AsCached();
            Container.Bind<UpdateBeginnerMissionAndPassStatusUseCase>().AsCached();
            Container.Bind<UpdatePartyMemberSlotUseCase>().AsCached();
            Container.BindInterfacesTo<HeldPassEffectDisplayModelFactory>().AsCached();
            Container.BindInterfacesTo<HeldAdSkipPassInfoModelFactory>().AsCached();
            Container.Bind<GetHeldAdSkipPassInfoUseCase>().AsCached();

            Container.BindInterfacesTo<LoginAnnouncementEvaluator>().AsCached();
            Container.BindInterfacesTo<AnnouncementDateTimeApplier>().AsCached();

            Container.Bind<HomeHeaderBadgeUseCase>().AsCached();
            Container.Bind<HomeHeaderIconUseCase>().AsCached();

            Container.BindInterfacesTo<ShopCacheRepository>().AsCached();
            Container.BindInterfacesTo<OutpostArtworkCacheRepository>().AsCached();

            Container.Bind<GetUnitSortAndFilterUseCase>().AsCached();
            Container.Bind<UnitSortAndFilterDialogViewModelTranslator>().AsCached();

            Container.BindInterfacesTo<QuestCacheRepository>().AsCached();

            Container.Bind<UserStoreInfoUseCase>().AsCached();
            Container.BindViewFactoryInfo<AgeConfirmationDialogViewController, AgeConfirmationDialogViewControllerInstaller>();

            Container.Bind<AdventBattleRankingResultUseCase>().AsCached();

            Container.BindInterfacesTo<AdventBattleRankingResultModelFactory>().AsCached();
            Container.BindInterfacesTo<InAppReviewWireFrame>().AsCached();

            Container.Bind<RecommendPartyFormationUseCase>().AsCached();
            Container.Bind<FetchHomeDataUseCase>().AsCached();

            Container.Bind<GetUserMaxStaminaUseCase>().AsCached();

            Container.BindInterfacesTo<HomeContentMaintenanceHandler>().AsCached();
        }

        void InstallUtil()
        {
            // 汎用的に使うもの
            Container.BindInterfacesTo<OutGameAssetUnLoader>().AsCached();
            Container.BindInterfacesTo<OutGameUnitImageLoader>().AsCached();
            Container.BindInterfacesTo<EventTopBackGroundLoader>().AsCached();
            Container.Bind<IAssetReferenceContainerRepository>()
                .WithId(TemplateInjectId.AssetContainer.OutGame)
                .To<AssetReferenceContainerRepository>()
                .AsCached();
            Container.BindInterfacesAndSelfTo<ItemDetailUtil>()
                .AsCached()
                .NonLazy();
            Container.Bind<UnreceivedRewardWireframe>().AsCached();

            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
        }

        void InstallHome()
        {
            //アウトゲーム画面を管理
            Container.BindInterfacesAndSelfTo<HomeUseCases>().AsCached();
            Container.Bind<HomeFooterBalloonUseCase>().AsCached();
        }

        void InstallHomeMain()
        {
            Container.BindViewFactoryInfo<HomeMainViewController, HomeMainViewControllerInstaller>();

            // ホームメイン「でのみ」使っているものを入れる
            Container.BindViewFactoryInfo<HomeStageSelectViewController, HomeStageSelectViewControllerInstaller>();
            Container.BindViewFactoryInfo<ItemBoxViewController, ItemBoxViewControllerInstaller>();
        }

        void InstallTradeShop()
        {
            Container.BindViewFactoryInfo<FragmentTradeShopTopViewController, FragmentTradeShopTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<ExchangeContentTopViewController, ExchangeContentTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<ExchangeShopTopViewController, ExchangeShopTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<ExchangeConfirmViewController,ExchangeConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<ArtworkExpandDialogViewController, ArtworkExpandDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                ArtworkFragmentAcquisitionViewController,
                ArtworkFragmentAcquisitionViewControllerInstaller>();
            Container.BindInterfacesTo<InGameResultFreePartTutorialContext>().AsCached();
            Container.BindFactory<ArtworkFragmentTutorialSequence,
                PlaceholderFactory<ArtworkFragmentTutorialSequence>>().AsCached();
            Container.Bind<CheckOpenExchangeContentUseCase>().AsCached();
            Container.Bind<GetActiveExchangeShopLineupIdUseCase>().AsCached();
            Container.BindInterfacesTo<ActiveExchangeShopLineupIdProvider>().AsCached();
        }

        void InstallStageStart()
        {
            Container.Bind<HomeStartStageUseCase>().AsCached();
            Container.Bind<HomeStageSelectUseCases>().AsCached();
            Container.BindInterfacesTo<SelectStageInteractor>().AsCached();
            Container.BindInterfacesTo<SpeedAttackUseCaseModelFactory>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentCompleteEvaluator>().AsCached();
            Container.BindInterfacesTo<ShowStageReleaseAnimationFactory>().AsCached();
            Container.Bind<GetStaminaUseCase>().AsCached();
            Container.Bind<StageLimitStatusUseCase>().AsCached();
            Container.BindInterfacesTo<StageSelectPresenter>().AsCached();
            Container.Bind<StartStageWireFrame>().AsCached();
            Container.Bind<CheckStaminaBoostAvailabilityUseCase>().AsCached();
        }

        void InstallNavigator()
        {
            // Home画面以外から別画面に遷移するための内容
            Container.BindInterfacesAndSelfTo<HomeMissionNavigator>().AsCached();
            Container.Bind<GachaListUseCase>().AsCached();
            Container.Bind<SkipEventDailyBonusAnimationUseCase>().AsCached();
            Container.Bind<MissionClearOnCallUseCase>().AsCached();
            // Home画面入ったときの画面コンテキスト復帰向け
            Container.Bind<SceneWireFrameUseCase>().AsCached();
            Container.BindInterfacesAndSelfTo<QuestOpenStatusEvaluator>().AsCached();
            Container.BindInterfacesAndSelfTo<QuestReleaseCheckSampleFinder>().AsCached();
            Container.Bind<EventOpenCheckUseCase>().AsCached();
            Container.Bind<EventQuestOpenCheckUseCase>().AsCached();
            Container.Bind<CheckPvpOpenUseCase>().AsCached();
            Container.BindInterfacesAndSelfTo<PvpOpeningStatusAtUserStatusModelFactory>().AsCached();
            Container.Bind<SceneWireFrame>().AsCached();
        }

        void InstallHelpers()
        {
            Container.BindInterfacesTo<UnitStatusCalculateHelper>().AsCached();
            Container.BindInterfacesTo<UnitEnhanceNotificationHelper>().AsCached();
            Container.BindInterfacesTo<ArtworkPanelHelper>().AsCached();
            Container.BindInterfacesTo<SeriesPrefixWordSortHelper>().AsCached();
            Container.BindInterfacesTo<UnitListFilterAndSort>().AsCached();
        }

        void InstallSelectFragmentItemBox()
        {
            Container.BindViewFactoryInfo<SelectionFragmentBoxViewController, SelectionFragmentBoxViewControllerInstaller>();
            Container.BindViewFactoryInfo<ExchangeShopConfirmViewController, ExchangeShopConfirmViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                SelectFragmentItemBoxTransitViewController,
                SelectFragmentItemBoxTransitViewInstaller>();
            Container.BindViewFactoryInfo<FragmentBoxTradeViewController, FragmentBoxTradeViewControllerInstaller>();
            Container.Bind<SelectionFragmentBoxWireFrame>().AsCached();
            Container.Bind<FragmentBoxTradeWireFrame>().AsCached();
            Container.Bind<ActiveItemWireFrame>().AsCached();
            Container.Bind<ActiveItemUseCase>().AsCached();
        }
        void InstallGacha()
        {
            Container.Bind<GetGachaNoticeUseCase>().AsCached();
            Container.BindViewFactoryInfo<GachaListViewController, GachaListViewControllerInstaller>();
            Container.BindInterfacesTo<GachaCacheRepository>().AsCached();
            Container.BindInterfacesTo<GachaEvaluator>().AsCached();
            Container.Bind<GachaWireFrame.Presentation.Presenters.GachaWireFrame>().AsCached();

            // ガシャ提供割合
            Container.BindViewFactoryInfo<GachaRatioDialogViewController, GachaRatioViewControllerInstaller>();
            Container.BindViewFactoryInfo<GachaLineupDialogViewController, GachaLineupDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<GachaDetailDialogViewController, GachaDetailDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<GachaDetailAnnouncementWebViewController, GachaDetailWebViewControllerInstaller>();
            Container.BindViewFactoryInfo<GachaDetailCautionWebViewController, GachaDetailCautionWebViewControllerInstaller>();
            Container.Bind<GetCommonReceiveItemUseCase>().AsCached();
        }

        void InstallTutorial()
        {
            Container.Install<TutorialInstaller>();
            // チュートリアル進捗更新
            Container.Bind<ProgressTutorialStatusUseCase>().AsCached();
            Container.BindInterfacesTo<TutorialStatusApplier>().AsCached();
            Container.BindInterfacesTo<UserTutorialFreePartModelsApplier>().AsCached();
            Container.Bind<CheckTutorialCompletedUseCase>().AsCached();
            Container.Bind<TutorialPartyFormationUseCase>().AsCached();
            Container.Bind<TutorialGachaDrawUseCase>().AsCached();
            Container.Bind<CheckConfirmTutorialGachaUseCase>().AsCached();
            Container.Bind<EnabledBackKeyInTutorialUseCase>().AsCached();
            Container.Bind<GachaConfirmedApplyUseCase>().AsCached();
            Container.Bind<TutorialApplyPartyFormationUseCase>().AsCached();
            Container.BindInterfacesTo<TutorialBackKeyHandler>().AsCached();

            Container.Bind<TutorialTipDialogUseCase>().AsCached();
            Container.Bind<TutorialTipDialogViewWireFrame>().AsCached();
            Container.Bind<CompleteFreePartTutorialUseCase>().AsCached();
            Container.Bind<CheckFreePartTutorialCompletedUseCase>().AsCached();

            Container.BindViewFactoryInfo<
                TutorialGachaReDrawDialogViewController,
                TutorialGachaReDrawDialogViewControllerInstaller>();
        }
        void InstallAdventBattle()
        {
            Container.BindViewFactoryInfo<AdventBattleTopViewController, AdventBattleTopViewControllerInstaller>();
            Container.BindViewFactoryInfo<EventBonusUnitListViewController, EventBonusUnitListViewControllerInstaller>();
            Container.BindViewFactoryInfo<AdventBattleInfoViewController, AdventBattleInfoViewControllerInstaller>();
            Container.BindViewFactoryInfo<AdventBattleRankingViewController, AdventBattleRankingViewInstaller>();
            Container.BindViewFactoryInfo<
                AdventBattleRaidRankingResultViewController,
                AdventBattleRaidRankingResultViewInstaller>();
            Container.BindViewFactoryInfo<AdventBattleMissionViewController, AdventBattleMissionViewControllerInstaller>();
            Container.BindViewFactoryInfo<AdventBattleRewardListViewController, AdventBattleRewardListViewControllerInstaller>();
            Container.BindViewFactoryInfo<AdventBattleRankingResultViewController, AdventBattleRankingResultViewInstaller>();
            Container.BindInterfacesTo<PartyStatusModelFactory>().AsCached();
            Container.BindInterfacesTo<AdventBattleDateTimeEvaluator>().AsCached();
        }

        void InstallEvent()
        {
            Container.BindViewFactoryInfo<EventQuestSelectViewController, EventQuestSelectViewInstaller>();
            Container.BindViewFactoryInfo<EventQuestTopViewController, EventQuestTopViewInstaller>();
            Container.BindViewFactoryInfo<EventQuestTopLoadingViewController, EventQuestTopLoadingViewInstaller>();
        }

        void InstallPvp()
        {
            Container.BindViewFactoryInfo<PvpTopViewController, PvpTopViewInstaller>();
            Container.BindViewFactoryInfo<PvpTicketConfirmViewController, PvpTicketConfirmViewInstaller>();
            Container.Bind<PvpWireFrame>().AsCached();

            Container.Bind<PvpStartUseCase>().AsCached();
            Container.BindInterfacesTo<PvpStartModelFactory>().AsCached();

            Container.BindViewFactoryInfo<PvpRewardListViewController, PvpRewardListViewControllerInstaller>();
            Container.BindViewFactoryInfo<PvpInfoViewController,PvpInfoViewControllerInstaller>();
            Container.BindViewFactoryInfo<PvpOpponentDetailViewController, PvpOpponentDetailViewInstaller>();

            Container.BindViewFactoryInfo<PvpRankingViewController, PvpRankingViewInstaller>();

            Container.BindViewFactoryInfo<PvpBattleResultViewController, PvpBattleResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpPreviousSeasonResultViewController,
                PvpPreviousSessionResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpNewSeasonStartViewController,
                PvpNewSeasonStartViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpBattleFinishAnimationViewController,
                PvpBattleFinishAnimationViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpBattleResultRankUpEffectViewController,
                PvpBattleResultRankUpEffectViewControllerInstaller>();
        }

        void InstallInAppPurchase()
        {
            Container.Bind<GetHomeDeferredPurchaseUseCase>().AsCached();
        }

        void InstallDebug()
        {
#if GLOW_DEBUG
            Container.Bind<HomeDebugCommandHandler>().AsSingle();
#endif
        }
    }
}
