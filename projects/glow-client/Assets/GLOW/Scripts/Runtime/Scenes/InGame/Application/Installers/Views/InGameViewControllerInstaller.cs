using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Presentation.SceneNavigation;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.GameOption.Domain.UseCases;
using GLOW.Modules.Tutorial.Application.Installers;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattleResult.Application.Installers.View;
using GLOW.Scenes.AdventBattleResult.Domain.Factory;
using GLOW.Scenes.AdventBattleResult.Presentation.Factory;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using GLOW.Scenes.AgeConfirm.Application.Views;
using GLOW.Scenes.AgeConfirm.Domain;
using GLOW.Scenes.AgeConfirm.Presentation.View;
using GLOW.Scenes.ArtworkFragmentAcquisition.Application.Installers;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.BattleResult.Application.Installers.Views;
using GLOW.Scenes.BattleResult.Domain.Appliers;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Executors;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.UseCases;
using GLOW.Scenes.BattleResult.Presentation.Presenters;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using GLOW.Scenes.EmblemDetail.Application;
using GLOW.Scenes.EmblemDetail.Presentation.Views;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.GachaCostItemDetailView.Application.Views;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.InGame.Data.Repositories;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Battle.InGameInitializers;
using GLOW.Scenes.InGame.Domain.Battle.Logger;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Battle.UpdateProcess;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using GLOW.Scenes.InGame.Presentation.Modules.Systems;
using GLOW.Scenes.InGame.Presentation.Navigation;
using GLOW.Scenes.InGame.Presentation.Presenters;
using GLOW.Scenes.InGame.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Views.DefeatDialog;
using GLOW.Scenes.InGame.Presentation.Views.InGameMenu;
using GLOW.Scenes.InGame.Presentation.Views.InGamePause;
using GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail;
using GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog;
using GLOW.Scenes.ItemDetail.Domain.Factory;
using GLOW.Scenes.ItemDetail.Domain.UseCase;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.PassShop.Domain.Factory;
using GLOW.Scenes.PvpBattleResult.Application.View;
using GLOW.Scenes.PvpBattleResult.Presentation.Factory;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using GLOW.Scenes.UnitReceive.Application.View;
using GLOW.Scenes.UnitReceive.Domain.UseCase;
using GLOW.Scenes.UnitReceive.Presentation.View;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PvpBattleFinishAnimation.Application.View;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.StaminaRecover.Application;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaTrade;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.UnitDetailModal.Application.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UserLevelUp.Application.Installer.View;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Battle.InGameInitializers;
using GLOW.Scenes.InGame.Data.Repositories.Debug;
using GLOW.Debugs.InGame.Installers;
using GLOW.Debugs.InGame.Presentation.DebugIngameLogView;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.InGame.Presentation.DebugCommands;
#endif

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGameViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<InGameViewController>();
            Container.BindInterfacesTo<InGamePresenter>().AsCached();

            Container.BindInterfacesTo<InGameSettingRepository>().AsCached();
            Container.BindInterfacesTo<InGameLogRepository>().AsCached();

            Container.BindInterfacesTo<InGameScene>().AsCached();

            Container.BindInterfacesTo<HPCalculator>().AsCached();
            Container.BindInterfacesTo<AttackFeedbackHPCalculator>().AsCached();
            Container.BindInterfacesTo<MarchingLaneDirector>().AsCached();
            Container.BindInterfacesTo<BuffStatePercentageConverter>().AsCached();
            Container.BindInterfacesTo<InGameUnitStatusCalculator>().AsCached();
            Container.BindInterfacesTo<RushChargeTimeCalculator>().AsCached();
            Container.BindInterfacesTo<OutpostMaxHpCalculator>().AsCached();
            Container.BindInterfacesTo<UnitSummonCoolTimeCalculator>().AsCached();
            Container.BindInterfacesTo<UnitSpecialAttackCoolTimeCalculator>().AsCached();
            Container.BindInterfacesTo<BattlePointChargeAmountCalculator>().AsCached();
            Container.BindInterfacesTo<MaxBattlePointCalculator>().AsCached();
            Container.BindInterfacesTo<InGameLogger>().AsCached();
            Container.BindInterfacesTo<NearestTargetFinder>().AsCached();

            Container.BindInterfacesTo<FieldObjectIdProvider>().AsCached();
            Container.BindInterfacesTo<StateEffectSourceIdProvider>().AsCached();
            Container.Bind<IInGameUnitEncyclopediaEffectProvider>()
                .WithId(InGameUnitEncyclopediaBindIds.Player)
                .To<InGameUnitEncyclopediaEffectProvider>().AsCached();
            Container.Bind<IInGameUnitEncyclopediaEffectProvider>()
                .WithId(InGameUnitEncyclopediaBindIds.PvpOpponent)
                .To<InGamePvpOpponentUnitEncyclopediaEffectProvider>().AsCached();
            Container.BindInterfacesTo<InGameEventBonusUnitEffectProvider>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleUnitStatusProvider>().AsCached();
            Container.BindInterfacesTo<StateEffectChecker>().AsCached();

            Container.BindInterfacesTo<DeckUnitSummonEvaluator>().AsCached();
            Container.BindInterfacesTo<DeckUnitSummonExecutor>().AsCached();
            Container.BindInterfacesTo<DeckUnitSpecialAttackEvaluator>().AsCached();
            Container.BindInterfacesTo<DeckUnitSpecialAttackExecutor>().AsCached();

            Container.BindInterfacesTo<DeckSpecialUnitSummonEvaluator>().AsCached();
            Container.BindInterfacesTo<DeckSpecialUnitSummonExecutor>().AsCached();
            Container.BindInterfacesTo<DeckSpecialUnitSummonPositionShifter>().AsCached();
            Container.BindInterfacesTo<DeckSpecialUnitSummonPositionSelector>().AsCached();

            Container.BindInterfacesTo<OutpostFactory>().AsCached();
            Container.BindInterfacesTo<CharacterUnitFactory>().AsCached();
            Container.BindInterfacesTo<SpecialUnitFactory>().AsCached();
            Container.BindInterfacesTo<InGameGimmickObjectFactory>().AsCached();
            Container.BindInterfacesTo<AttackModelFactory>().AsCached();
            Container.BindInterfacesTo<AttackResultModelFactory>().AsCached();
            Container.BindInterfacesTo<AttackFeedbackModelFactory>().AsCached();
            Container.BindInterfacesTo<StateEffectModelFactory>().AsCached();
            Container.BindInterfacesTo<KomaModelFactory>().AsCached();
            Container.Bind<InitialEnemyCharacterCoefFactory>().AsCached();
            Container.BindInterfacesTo<BattleEndConditionModelFactory>().AsCached();
            Container.BindInterfacesTo<CommonConditionModelFactory>().AsCached();
            Container.BindInterfacesTo<MangaAnimationModelFactory>().AsCached();
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();
            Container.BindInterfacesTo<AutoPlayerSequenceElementStateModelFactory>().AsCached();
            Container.BindInterfacesTo<UnitGenerationModelFactory>().AsCached();
            Container.BindInterfacesTo<InGameGimmickObjectGenerationModelFactory>().AsCached();
            Container.BindInterfacesTo<ScoreCalculateModelFactory>().AsCached();
            Container.BindInterfacesTo<CharacterUnitActionFactory>().AsCached();
            Container.BindInterfacesTo<DefenseTargetFactory>().AsCached();
            Container.BindInterfacesTo<OutpostEnhancementModelFactory>().AsCached();
            Container.BindInterfacesTo<UnitAbilityModelFactory>().AsCached();

            Container.BindInterfacesTo<InGamePreferenceInitializer>().AsCached();
            Container.BindInterfacesTo<EnemyAutoPlayerInitializer>().AsCached();
            Container.BindInterfacesTo<PlayerAutoPlayerInitializer>().AsCached();
            Container.BindInterfacesTo<InitialEnemySummonInitializer>().AsCached();
            Container.BindInterfacesTo<InGameGimmickObjectInitializer>().AsCached();
            Container.BindInterfacesTo<DefenseTargetInitializer>().AsCached();
            Container.BindInterfacesTo<BattleSpeedInitializer>().AsCached();
            Container.BindInterfacesTo<SpecialAttackCutInLogInitializer>().AsCached();
            Container.BindInterfacesTo<RushInitializer>().AsCached();
            Container.BindInterfacesTo<ScoreInitializer>().AsCached();
            Container.BindInterfacesTo<OutpostEnhancementInitializer>().AsCached();
            Container.BindInterfacesTo<StageQuestInitializer>().AsCached();
            Container.BindInterfacesTo<BattleEndConditionInitializer>().AsCached();
            Container.BindInterfacesTo<OutpostInitializer>().AsCached();
            Container.BindInterfacesTo<DeckInitializer>().AsCached();
            Container.BindInterfacesTo<BattlePointInitializer>().AsCached();
            Container.BindInterfacesTo<ArtworkBonusHpInitializer>().AsCached();

            Container.BindInterfacesTo<CharacterUnitUpdateProcess>().AsCached();
            Container.BindInterfacesTo<SpecialUnitUpdateProcess>().AsCached();
            Container.BindInterfacesTo<AttackProcess>().AsCached();
            Container.BindInterfacesTo<StateEffectUpdateProcess>().AsCached();
            Container.BindInterfacesTo<BattlePointUpdateProcess>().AsCached();
            Container.BindInterfacesTo<AutoPlayerUpdateProcess>().AsCached();
            Container.BindInterfacesTo<RushUpdateProcess>().AsCached();
            Container.BindInterfacesTo<KomaEffectProcess>().AsCached();
            Container.BindInterfacesTo<UnitAbilityProcess>().AsCached();
            Container.BindInterfacesTo<BossSummonQueueUpdateProcess>().AsCached();
            Container.BindInterfacesTo<UnitSummonQueueUpdateProcess>().AsCached();
            Container.BindInterfacesTo<SpecialUnitSummonQueueUpdateProcess>().AsCached();
            Container.BindInterfacesTo<GimmickObjectToEnemyTransformationUpdateProcess>().AsCached();
            Container.BindInterfacesTo<MangaAnimationUpdateProcess>().AsCached();
            Container.BindInterfacesTo<BossAppearancePauseUpdateProcess>().AsCached();
            Container.BindInterfacesTo<UnitRemovingProcess>().AsCached();
            Container.BindInterfacesTo<GimmickObjectRemovingProcess>().AsCached();
            Container.BindInterfacesTo<DeckUpdateProcess>().AsCached();
            Container.BindInterfacesTo<UnitTransformationProcess>().AsCached();
            Container.BindInterfacesTo<StageTimeUpdateProcess>().AsCached();
            Container.BindInterfacesTo<BattleEndCheckProcess>().AsCached();
            Container.BindInterfacesTo<SpeechBalloonProcess>().AsCached();
            Container.BindInterfacesTo<ScoreUpdateProcess>().AsCached();
            Container.BindInterfacesTo<UpdatePlacedItemsProcess>().AsCached();

            Container.Bind<InitializeInGameUseCase>().AsCached();
            Container.Bind<UpdateBattleUseCase>().AsCached();
            Container.Bind<SummonUserCharacterUseCase>().AsCached();
            Container.Bind<SummonSpecialUnitUseCase>().AsCached();
            Container.Bind<UseSpecialAttackUseCase>().AsCached();
            Container.Bind<StartSpecialUnitSummonUseCase>().AsCached();
            Container.Bind<CancelSpecialUnitSummonUseCase>().AsCached();
            Container.Bind<SpecialUnitSummonConfirmationDialogUseCase>().AsCached();
            Container.Bind<ChangeBattleSpeedUseCase>().AsCached();
            Container.Bind<VictoryUseCase>().AsCached();
            Container.Bind<DefeatUseCase>().AsCached();
            Container.Bind<AbortUseCase>().AsCached();
            Container.Bind<PvpGiveUpUseCase>().AsCached();
            Container.Bind<ShowInGameMenuUseCase>().AsCached();
            Container.Bind<SwitchBgmGameOptionUseCase>().AsCached();
            Container.Bind<SwitchSeGameOptionUseCase>().AsCached();
            Container.Bind<SetSpecialAttackCutInPlayTypeGameOptionUseCase>().AsCached();
            Container.Bind<SwitchTwoRowDeckGameOptionUseCase>().AsCached();
            Container.Bind<SwitchDamageDisplayGameOptionUseCase>().AsCached();
            Container.Bind<SaveInGameOptionFinishedUseCase>().AsCached();
            Container.Bind<GetContinueDiamondUseCase>().AsCached();
            Container.Bind<GetContinueActionSelectionUseCase>().AsCached();
            Container.Bind<ContinueDiamondUseCase>().AsCached();
            Container.Bind<ContinueAdUseCase>().AsCached();
            Container.Bind<ChangeDeckUseCase>().AsCached();
            Container.Bind<PreVictoryUseCase>().AsCached();
            Container.Bind<PreFinishUseCase>().AsCached();
            Container.Bind<UserStoreInfoUseCase>().AsCached();
            Container.Bind<ExecuteRushUseCase>().AsCached();
            Container.Bind<SwitchAutoUseCase>().AsCached();

            Container.BindInterfacesTo<ContinueExecutor>().AsCached();

            Container.BindInterfacesTo<NextReleaseAnimationApplier>().AsCached();
            Container.BindInterfacesTo<UserExpGainModelsFactory>().AsCached();
            Container.BindInterfacesTo<TutorialVictoryResultModelFactory>().AsCached();
            Container.BindInterfacesTo<AdventBattleVictoryResultModelFactory>().AsCached();
            Container.BindInterfacesTo<StageVictoryResultModelFactory>().AsCached();
            Container.BindInterfacesTo<AcquiredPlayerResourceModelFactory>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentAcquisitionModelFactory>().AsCached();
            Container.BindInterfacesTo<EnemyCountResultModelFactory>().AsCached();
            Container.BindInterfacesTo<ResultScoreModelFactory>().AsCached();
            Container.BindInterfacesTo<ResultSpeedAttackModelFactory>().AsCached();

            Container.Bind<IAutoPlayer>().WithId(AutoPlayer.PlayerAutoPlayerBindId).To<AutoPlayer>().AsCached();
            Container.Bind<IAutoPlayer>().WithId(AutoPlayer.EnemyAutoPlayerBindId).To<AutoPlayer>().AsCached();

            Container.BindInterfacesTo<InitialAssetLoader>().AsCached();
            Container.BindInterfacesTo<KomaBackgroundLoader>().AsCached();
            Container.BindInterfacesTo<KomaEffectPrefabLoader>().AsCached();
            Container.BindInterfacesTo<MangaAnimationLoader>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<InGameStartAnimationViewController, InGameStartAnimationViewControllerInstaller>();
            Container.BindViewFactoryInfo<InGamePauseViewController, InGamePauseViewControllerInstaller>();
            Container.BindViewFactoryInfo<InGameMenuViewController, InGameMenuViewControllerInstaller>();
            Container.BindViewFactoryInfo<DefeatDialogViewController, DefeatDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<VictoryAnimationViewController, InGameVictoryAnimationViewControllerInstaller>();
            Container.BindViewFactoryInfo<VictoryResultViewController, InGameVictoryResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<DefeatResultViewController, InGameDefeatResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<FinishAnimationViewController, InGameFinishAnimationViewControllerInstaller>();
            Container.BindViewFactoryInfo<FinishResultViewController, InGameFinishResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<AdventBattleResultViewController, AdventBattleResultViewControllerInstaller>();

            Container.BindViewFactoryInfo<PvpBattleResultViewController, PvpBattleResultViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpBattleFinishAnimationViewController,
                PvpBattleFinishAnimationViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                PvpBattleResultRankUpEffectViewController,
                PvpBattleResultRankUpEffectViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                ContinueActionSelectionViewController,
                ContinueActionSelectionViewControllerInstaller>();
            Container.BindViewFactoryInfo<DiamondPurchaseViewController, DiamondPurchaseViewControllerInstaller>();
            Container.BindViewFactoryInfo<InGameUnitDetailViewController, InGameUnitDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<AgeConfirmationDialogViewController, AgeConfirmationDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UserLevelUpViewController, UserLevelUpResultViewControllerInstaller>();

            Container.BindViewFactoryInfo<
                ArtworkFragmentAcquisitionViewController,
                ArtworkFragmentAcquisitionViewControllerInstaller>();

            Container.BindViewFactoryInfo<
                AdventBattleResultRankUpEffectViewController,
                AdventBattleResultRankUpEffectViewControllerInstaller>();

            Container.BindViewFactoryInfo<
                SpecialUnitSummonConfirmationDialogViewController,
                SpecialUnitSummonConfirmationDialogViewControllerInstaller>();

            Container.BindInterfacesTo<UnitStatusCalculator>().AsCached();
            Container.BindInterfacesTo<UnitStatusCalculateHelper>().AsCached();

            Container.BindInterfacesTo<InGameDummyHomeHeaderPresenter>().AsCached();

            Container.BindInterfacesTo<UserLevelUpResultViewFacade>().AsCached();

            Container.BindInterfacesTo<PartyStatusModelFactory>().AsCached();
            Container.BindInterfacesTo<InGameEndBattleLogModelFactory>().AsCached();
            Container.BindInterfacesTo<AdventBattleInGameEndBattleLogModelFactory>().AsCached();

            Container.BindInterfacesTo<AdventBattleScoreModelFactory>().AsCached();
            Container.BindInterfacesTo<AdventBattleResultScoreViewModelFactory>().AsCached();
            Container.BindInterfacesTo<AdventBattleResultScoreModelFactory>().AsCached();

            Container.BindInterfacesTo<PvpResultPointModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpInGameEndBattleLogModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpVictoryResultModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpBattleResultPointViewModelFactory>().AsCached();
            Container.BindInterfacesTo<PvpResultEvaluator>().AsCached();

            Container.Bind<PrefabFactory<KomaSetComponent>>().AsCached();
            Container.Bind<PrefabFactory<MangaAnimation>>().AsCached();

            Container.BindInterfacesTo<HeldAdSkipPassInfoModelFactory>().AsCached();
            Container.Bind<UnreceivedRewardWireframe>().AsCached();
            Container.BindInterfacesTo<PeriodOutsideExceptionWireframe>().AsCached();

            Container.Bind<CheckContentOpenUseCase>().AsCached();

            Container.Bind<InGameSessionCleanupUseCase>().AsCached();
            Container.BindInterfacesTo<InGameContentMaintenanceHandler>().AsCached();

            InstallDebugBindings();
            InstallItemDetailBindings();
            InstallUnitReceiveBindings();
            InstallTutorial();
            InstallRetryBindings();

        }

        void InstallTutorial()
        {
            Container.Install<InGameTutorialInstaller>();
        }

        void InstallRetryBindings()
        {
            // 再挑戦
            Container.BindInterfacesTo<EnhanceQuestModelFactory>().AsCached();
            Container.BindInterfacesTo<InGameRetryEvaluator>().AsCached();
            Container.BindInterfacesTo<InGameRetrySceneNavigator>().AsCached();
            Container.BindInterfacesTo<GlowSceneNavigation>().AsCached();
            Container.Bind<AdventBattleWireFrame>().AsCached();
            Container.Bind<AdventBattleStartUseCase>().AsCached();
            Container.Bind<RetryPeriodOutsideUseCase>().AsCached();
            Container.Bind<StartStageWireFrame>().AsCached();
            Container.Bind<HomeStartStageUseCase>().AsCached();
            Container.BindInterfacesTo<SelectStageInteractor>().AsCached();

            // スタミナブースト
            Container.Bind<InGameRetryStaminaBoostUseCase>().AsCached();
            Container.BindInterfacesTo<GLOW.Scenes.StaminaBoostDialog.Domain.Evaluator.StaminaBoostEvaluator>().AsCached();
            Container.BindViewFactoryInfo<
                GLOW.Scenes.StaminaBoostDialog.Presentation.View.StaminaBoostDialogViewController,
                GLOW.Scenes.StaminaBoostDialog.Application.Installer.StaminaBoostDialogViewControllerInstaller>();

            // スタミナ回復
            Container.BindInterfacesTo<GLOW.Scenes.StaminaRecover.Domain.Factory.UserStaminaModelFactory>().AsCached();
            Container.BindViewFactoryInfo<
                GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect.StaminaRecoverSelectViewController,
                GLOW.Scenes.StaminaRecover.Application.StaminaRecoverViewInstaller>();
            Container.BindViewFactoryInfo<StaminaRecoverySelectViewController, StaminaRecoverySelectViewControllerInstaller>();
            Container.BindViewFactoryInfo<StaminaTradeViewController, StaminaTradeViewControllerInstaller>();
            Container.BindViewFactoryInfo<StaminaDiamondRecoverConfirmViewController,StaminaDiamondRecoverConfirmViewControllerInstaller>();

            // InGameではスタミナ回復ダイアログの一部機能のみ使用するため、インターフェース化してPresentModally()メソッドのみ提供
            Container.BindInterfacesTo<InGameHomeViewController>().AsCached();

            // StaminaRecoverSelectPresenterが依存するIHomeUseCasesのダミーバインド
            // IHomeUseCases: GetUserParameter()とGetUserProfile()メソッドを提供
            Container.BindInterfacesTo<InGameDummyHomeUseCases>().AsCached();

            // StaminaRecoverSelectPresenterが依存するその他のUseCase
            Container.Bind<GLOW.Scenes.PassShop.Domain.UseCase.GetHeldAdSkipPassInfoUseCase>().AsCached();
        }

        void InstallItemDetailBindings()
        {
            Container.BindInterfacesTo<ItemDetailWireFrame>().AsCached();
            Container.Bind<ShowItemDetailUseCase>().AsCached();

            Container.BindInterfacesTo<PlayerResourceModelFactory>().AsCached();
            Container.BindInterfacesTo<ItemDetailAmountModelFactory>().AsCached();
            Container.BindInterfacesTo<ItemDetailAvailableLocationModelFactory>().AsCached();
            Container.BindInterfacesTo<DummyHomeViewControlInInGame>().AsCached();
            Container.BindViewFactoryInfo<GachaCostItemDetailViewController, GachaCostItemDetailViewControllerInstaller>();

            Container.BindViewFactoryInfo<
                EmblemDetailViewController,
                EmblemDetailViewControllerInstaller>();

            //ユニットDetail
            Container.BindViewFactoryInfo<UnitDetailModalViewController, UnitDetailModalViewControllerInstaller>();
            Container.BindInterfacesTo<InGameDummyHomeViewNavigation>().AsCached();
            Container.BindInterfacesTo<UnitEnhanceAbilityModelListFactory>().AsCached();
            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
        }

        void InstallUnitReceiveBindings()
        {
            Container.Bind<DisplayReceivedUnitUseCase>().AsCached();
            Container.BindInterfacesTo<UnitReceiveWireFrame>().AsCached();
            Container.BindViewFactoryInfo<UnitReceiveViewController, UnitReceiveViewControllerInstaller>();
        }

        void InstallDebugBindings()
        {
#if GLOW_INGAME_DEBUG
            Container.Bind<DebugGetDebugModelUseCase>().AsCached();
            Container.Bind<DebugMaximizeBattlePointUseCase>().AsCached();
            Container.Bind<DebugChangeSummonCostToZeroUseCase>().AsCached();
            Container.Bind<DebugResetSummonCostUseCase>().AsCached();
            Container.Bind<DebugToggleBattlePauseUseCase>().AsCached();
            Container.Bind<DebugChangeSpecialAttackCoolTimeToZeroUseCase>().AsCached();
            Container.Bind<DebugResetSpecialAttackCoolTimeUseCase>().AsCached();
            Container.Bind<DebugVictoryUseCase>().AsCached();
            Container.Bind<DebugDefeatUseCase>().AsCached();
            Container.Bind<DebugChangeCharacterUnitDamageInvalidationUseCase>().AsCached();
            Container.Bind<DebugGetCharacterUnits>().AsCached();
            Container.Bind<DebugSummonEnemyUnitUseCase>().AsCached();
            Container.Bind<DebugApplyStateEffectUseCase>().AsCached();
            Container.Bind<DebugChangeUnitStatusUseCase>().AsCached();
            Container.Bind<DebugChangeOutpostDamageInvalidationUseCase>().AsCached();
            Container.Bind<DebugSetUnitHpToZeroUseCase>().AsCached();
            Container.Bind<DebugDisableKnockBackUseCase>().AsCached();
            Container.Bind<DebugAlwaysEnableRushUseCase>().AsCached();
            Container.Bind<DebugChangeOutpostEnhancementUseCase>().AsCached();
            Container.Bind<DebugToggleBattleStageTimePauseUseCase>().AsCached();

            Container.BindInterfacesTo<InGameDebugReportRepository>().AsCached();
            Container.Bind<InitialPlayerUnitCoefFactory>().AsCached();
            Container.BindViewFactoryInfo<DebugIngameLogViewerViewController, DebugIngameLogViewerViewInstaller>();

            Container.BindInterfacesTo<InGameDebugInitializer>().AsCached();

            // Bind debug command handler
            Container.Bind<InGameDebugCommandHandler>().AsCached();
#endif
        }
    }
}

