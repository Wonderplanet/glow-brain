using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Exceptions;
using GLOW.Core.Extensions;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Transitions;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Manager;
using GLOW.Scenes.AdventBattleResult.Presentation.Factory;
using GLOW.Scenes.AdventBattleResult.Presentation.View;
using GLOW.Scenes.AdventBattleResult.Presentation.ViewModel;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Translator;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.UseCases;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Presentation.Translators;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.InterruptAnimation;
using GLOW.Scenes.InGame.Presentation.Navigation;
using GLOW.Scenes.InGame.Presentation.Translators;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using GLOW.Scenes.InGame.Presentation.Views;
using GLOW.Scenes.InGame.Presentation.Views.DefeatDialog;
using GLOW.Scenes.InGame.Presentation.Views.InGameMenu;
using GLOW.Scenes.InGame.Presentation.Views.InGamePause;
using GLOW.Scenes.InGame.Presentation.Views.InGameUnitDetail;
using GLOW.Scenes.InGame.Presentation.Views.SpecialUnitSummonConfirmationDialog;
using GLOW.Scenes.Mission.Presentation.Presenter;
using GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View;
using GLOW.Scenes.PvpBattleResult.Presentation.Factory;
using GLOW.Scenes.PvpBattleResult.Presentation.View;
using GLOW.Scenes.StaminaBoostDialog.Presentation.View;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect;
using GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect;
using GLOW.Scenes.UserLevelUp.Presentation.Translator;
using UIKit;
using UnityEngine;
using UnityEngine.Profiling;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.Command.Presentations;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.InGame.Presentation.DebugCommands;
#endif

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    public class InGamePresenter : IInGameViewDelegate
        , IBattlePresenter
        , IFixedTickable
        , IInGamePauseControl
        , IInGameMenuSettingUpdateControl
        , ITutorialInGameViewDelegate
    {
        record CutInInfo(
            FieldObjectId Id,
            CharacterColor Color,
            UnitAssetKey AssetKey,
            UnitAttackViewInfo UnitAttackViewInfo,
            SpecialAttackCutInSelfPauseFlag SpecialAttackCutInSelfPauseFlag);

        [Inject] InGameViewController ViewController { get; }
        [Inject] InitializeInGameUseCase InitializeInGameUseCase { get; }
        [Inject] UpdateBattleUseCase UpdateBattleUseCase { get; }
        [Inject] SummonUserCharacterUseCase SummonUserCharacterUseCase { get; }
        [Inject] SummonSpecialUnitUseCase SummonSpecialUnitUseCase { get; }
        [Inject] StartSpecialUnitSummonUseCase StartSpecialUnitSummonUseCase { get; }
        [Inject] CancelSpecialUnitSummonUseCase CancelSpecialUnitSummonUseCase { get; }
        [Inject] SpecialUnitSummonConfirmationDialogUseCase SpecialUnitSummonConfirmationDialogUseCase { get; }
        [Inject] UseSpecialAttackUseCase UseSpecialAttackUseCase { get; }
        [Inject] ChangeBattleSpeedUseCase ChangeBattleSpeedUseCase { get; }
        [Inject] SwitchAutoUseCase SwitchAutoUseCase { get; }
        [Inject] VictoryUseCase VictoryUseCase { get; }
        [Inject] DefeatUseCase DefeatUseCase { get; }
        [Inject] GetContinueActionSelectionUseCase GetContinueActionSelectionUseCase { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IInGamePauseControl InGamePauseControl { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }
        [Inject] ShowInGameMenuUseCase ShowInGameMenuUseCase { get; }
        [Inject] SaveInGameOptionFinishedUseCase SaveInGameOptionFinishedUseCase { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] ChangeDeckUseCase ChangeDeckUseCase { get; }
        [Inject] IViewCoordinateConverter ViewCoordinateConverter { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] PreVictoryUseCase PreVictoryUseCase { get; }
        [Inject] PreFinishUseCase PreFinishUseCase { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] ISoundEffectLoader SoundEffectLoader { get; }
        [Inject] IIntroductionTutorialContext IntroductionTutorialContext { get; }
        [Inject] ITutorialContext TutorialContext { get; }
        [Inject] ExecuteRushUseCase ExecuteRushUseCase { get; }
        [Inject] IAdventBattleResultScoreViewModelFactory AdventBattleResultScoreViewModelFactory { get; }
        [Inject] IPvpBattleResultPointViewModelFactory PvpBattleResultPointViewModelFactory { get; }
        [Inject] BattleEffectManager BattleEffectManager { get; }
        [Inject] UIEffectManager UIEffectManager { get; }
        [Inject] BattleSummonEffectManager BattleSummonEffectManager { get; }
        [Inject] BattleScoreEffectManager BattleScoreEffectManager { get; }
        [Inject] BattleStateEffectViewManager BattleStateEffectViewManager { get; }
        [Inject] CheckTutorialCompletedUseCase CheckTutorialCompletedUseCase { get; }
        [Inject] TutorialTransitionSkipUseCase TutorialTransitionSkipUseCase { get; }
        [Inject] TutorialChargeRushGaugeUseCase TutorialChargeRushGaugeUseCase { get; }
        [Inject] TutorialChangeSummonCostToZeroUseCase TutorialChangeSummonCostToZeroUseCase { get; }
        [Inject] TutorialChangeFirstUnitSummonCostToZero TutorialChangeFirstUnitSummonCostToZero { get; }
        [Inject] TutorialChangeFirstUnitRemainingSpecialAttackCoolTimeToZeroUseCase
            TutorialChangeFirstUnitRemainingSpecialAttackCoolTimeToZeroUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] IntroductionTutorialSkipUseCase IntroductionTutorialSkipUseCase { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IInitialAssetLoader InitialAssetLoader { get; }
        [Inject] ITimeScaleController TimeScaleController { get; }
        [Inject] UnreceivedRewardWireframe UnreceivedRewardWireframe { get; }
        [Inject] IPeriodOutsideExceptionWireframe PeriodOutsideExceptionWireframe { get; }
        [Inject] AbortUseCase AbortUseCase { get; }
        [Inject] CheckContentOpenUseCase CheckContentOpenUseCase { get; }
        [Inject] IContentMaintenanceCoordinator ContentMaintenanceCoordinator { get; }
        [Inject] IContentMaintenanceHandler ContentMaintenanceHandler { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] IInGameRetrySceneNavigator InGameRetrySceneNavigator { get; }
        [Inject] InGameRetryStaminaBoostUseCase InGameRetryStaminaBoostUseCase { get; }

#if GLOW_INGAME_DEBUG
        [Inject] InGameDebugCommandHandler DebugCommandHandler { get; }
#endif

        bool _isStarted;
        bool _isBattleOver;
        bool _isPlayingTutorial; //チュートリアルが進行中か確認するために使用
        bool _isPlayingUnitDetailTutorial = false;
        InGameAutoEnabledFlag _isAutoEnabled;
        MstPageModel _mstPageModel;
        InGameMenuViewController _shownMenu;
        InGamePauseViewController _shownPause;
        StaminaRecoverySelectViewController _staminaRecoverySelectViewController;

        ITimeScaleControlHandler _battleSpeedTimeScaleControlHandler;
        BattleSpeed _battleSpeed = BattleSpeed.x1;

        bool _isSpecialUnitSummonSelecting;
        SpecialUnitSummonKomaRange _specialUnitSummonKomaRange = SpecialUnitSummonKomaRange.Empty;

        bool _isWaitingBossAppearancePauseEnd;
        BGMAssetKey _bossBGMAssetKey = BGMAssetKey.Empty;

        // 勝利条件
        IReadOnlyList<IBattleEndConditionModel> _battleEndConditions = new List<IBattleEndConditionModel>();

        // 残り時間表示
        List<TimeCountDown> _timeCountDowns = new List<TimeCountDown>();

        // 必殺ワザカットイン
        List<MasterDataId> _playedSpecialAttackCutInUnitIds = new(); // 必殺ワザカットインを再生したプレイヤーキャラ(1日1回)
        SpecialAttackCutInPlayType _specialAttackCutInPlayType; // 今のモードの状態

        // ダメージを表示するかどうか
        DamageDisplayFlag _isDamageDisplay;

        // コマExpanding管理
        KomaExpander _komaExpander;

        // 変身
        readonly UnitTransformationAnimationDirector _unitTransformationAnimationDirector = new();

        // スペシャルユニットのスキル使用確認ダイアログ
        SpecialUnitSummonConfirmationDialogViewController _specialUnitSummonConfirmationDialogViewController;

        // キャラ詳細
        InGameUnitDetailViewController _inGameUnitDetailViewController;

        // CancellationToken
        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();
        CancellationTokenSource _bossAppearanceAnimationCancellationTokenSource;
        CancellationTokenSource _battleStartScrollCancellationTokenSource;
        CancellationTokenSource _battleStartDefenseTargetHighlightCancellationTokenSource;
        CancellationTokenSource _battleStartPlayerOutpostHpHighlightCancellationTokenSource;
        CancellationTokenSource _battleStartMangaAnimationCancellationTokenSource;
        CancellationTokenSource _battleStartNoiseAnimationCancellationTokenSource;
        CancellationTokenSource _gameEndMangaAnimationCancellationTokenSource;
        CancellationTokenSource _rushAnimationCancellationTokenSource;
        List<CancellationTokenSource> _specialUnitSummonCancellationTokenSources = new();

        // バトル進行のポーズ管理
        readonly MultipleSwitchController _battlePauseController = new();
        MultipleSwitchHandler _appBackgroundPauseHandler;
        MultipleSwitchHandler _menuPauseHandler;
        MultipleSwitchHandler _gameEndPauseHandler;
        MultipleSwitchHandler _tutorialPauseHandler;
        MultipleSwitchHandler _rushPauseHandler;
        MultipleSwitchHandler _interruptAnimationPauseHandler;

        // InGameViewのポーズ管理
        MultipleSwitchHandler _viewPauseHandlerForTutorial;
        MultipleSwitchHandler _viewPauseHandlerForRush;

        // 演出中フラグ
        bool _isRushAnimationPlaying;

        // 割り込み演出管理
        readonly InGameInterruptAnimationDirector _inGameInterruptAnimationDirector = new InGameInterruptAnimationDirector();
        bool _isInterruptAnimationPlaying;

#if GLOW_INGAME_DEBUG
        // デバッグメニューを開いてるときのポーズ
        MultipleSwitchHandler _debugCommandPauseHandler;
        MultipleSwitchHandler _viewPauseHandlerForDebugCommand;

        // デバッグ機能の「バトルポーズ」用
        MultipleSwitchHandler _debugPauseHandler;
        MultipleSwitchHandler _debugViewPauseHandler;
#endif
        public bool IsEndTutorial { get; private set; } // チュートリアル中のインゲームが終了しているか確認するために使用

        void IInGameViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(InGamePresenter), nameof(IInGameViewDelegate.OnViewDidLoad));
            ViewController.ActualView.OnApplicationFocusAction = InGamePauseControl.Pause;
            ContentMaintenanceCoordinator.SetUp(ContentMaintenanceHandler);

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                ViewController.EnableTapGuard();

                // チュートリアル未完了の場合、オプションボタン、ラッシュゲージを非表示にする
                if (!CheckTutorialCompletedUseCase.CheckTutorialCompleted())
                {
                    ViewController.HideHeaderButton();
                    ViewController.HideRushGauge();
                }

                InitializeResultModel initializeResult = InitializeInGameUseCase.Initialize();
                _isAutoEnabled = initializeResult.IsAutoEnabled;
                _mstPageModel = initializeResult.MstPage;

                // KomaExpanderの初期化
                _komaExpander = new KomaExpander(ViewController, ViewCoordinateConverter, _mstPageModel);

                InitialAssetLoader.LoadInBackground(initializeResult.InitialLoadAssetsModel, cancellationToken);

                await SoundEffectLoader.Load(cancellationToken, SoundEffectTag.InGame);

                _specialAttackCutInPlayType = initializeResult.SpecialAttackCutInPlayType;
                _playedSpecialAttackCutInUnitIds = initializeResult.SpecialAttackCutInPlayedUnitIds.ToList();

                _isDamageDisplay = initializeResult.IsDamageDisplay;

                IReadOnlyList<DeckUnitViewModel> deckUnitViewModels = initializeResult.DeckUnits
                    .Select(model => model.ToViewModel(
                        initializeResult.BattlePointModel.CurrentBattlePoint,
                        CanSummonAnySpecialUnitFlag.True))
                    .ToList();
                _battleEndConditions = initializeResult.BattleEndModel.Conditions;

                // ボス登場時のBGM
                _bossBGMAssetKey = initializeResult.BossBGMAssetKey;

                var initializeViewModel = new InitializeViewModel(
                    StageNumberCaption.Create(initializeResult.InGameNumber),
                    initializeResult.InGameName,
                    deckUnitViewModels,
                    initializeResult.MstPage,
                    initializeResult.PlayerOutpost,
                    initializeResult.EnemyOutpost,
                    initializeResult.RushModel,
                    initializeResult.PvpOpponentRushModel,
                    initializeResult.InitialCharacterUnits,
                    initializeResult.InGameGimmickObjectModels,
                    initializeResult.DefenseTargetModel,
                    initializeResult.BattlePointModel,
                    initializeResult.BattleSpeed,
                    initializeResult.IsAutoEnabled,
                    initializeResult.InGameType,
                    initializeResult.QuestType,
                    initializeResult.StageTimeModel,
                    initializeResult.BattleEndModel
                );

                //NOTE: インゲームに２回目以降に遷移するなど、アセットがキャッシュされている状態だと
                //PageComponent._scrollRect.transformが初期化される前に処理が走るので、1フレーム待つ
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);

                await BattleEffectManager.Initialize(cancellationToken);
                await UIEffectManager.Initialize(cancellationToken);
                await BattleSummonEffectManager.Initialize(cancellationToken);
                await BattleScoreEffectManager.Initialize(cancellationToken);
                await BattleStateEffectViewManager.Initialize(cancellationToken);

                // アセットのロード完了を待つ
                await UniTask.WaitUntil(() => InitialAssetLoader.IsCompleted, cancellationToken: cancellationToken);

                await ViewController.Initialize(initializeViewModel, cancellationToken);

                // 導入チュートリアル用の漫画を読み込む
                await PreLoadIntroductionManga(cancellationToken);

                // 二列モードだったら一度呼んで切り開ける(デフォルトが一列)
                if (initializeResult.IsTwoRowDeck)
                {
                    ChangeDeckMode();
                }

                BackgroundMusicPlayable.Play(initializeResult.BGMAssetKey.Value);

                // コマの初期化処理
                SetUpKomas(initializeResult.KomaDictionary);

                ViewController.IsInitialized = true;

                SetupBattleSpeed(initializeResult.BattleSpeed);
                SetupTimeCountDown();

                if (initializeResult.IsInGameContinueSelecting)
                {
                    _isStarted = true;
                    _isSpecialUnitSummonSelecting = false;

                    ViewController.DisableTapGuard();
                    OnDefeatWithContinue(StageEndConditionType.None);
                    return;
                }

                // インゲーム開始前のチュートリアル実行
                await PlayIntroductionTutorial();

                await UniTask.Delay(50, cancellationToken: cancellationToken);

                // コンティニュー済みで中断復帰した際は開始演出群はスキップする
                if (!InGameScene.IsContinued)
                {
                    await PlayPreStartAnimation(initializeResult, cancellationToken);
                }

                // 1. ステージ開始演出中でもUI操作したいものを手前に持ってくる
                ViewController.SetButtonsToFrontViewCanvas();
                await ShowStartAnimationView(cancellationToken);

                // 2. もとに戻す
                ViewController.ResetMovedUI();

                _isStarted = true;
                _isSpecialUnitSummonSelecting = false;

                ViewController.DisableTapGuard();

                // チュートリアル実行
                await PlayTutorial();
            });

#if GLOW_INGAME_DEBUG
            // Initialize debug command handler
            DebugCommandHandler.PlayDebugCutIn = PlayDebugCutIn;
            DebugCommandHandler.DebugPause = DebugPause;

            DebugCommandActivator.OnDebugCommandActivated += DebugCommandActivated;
            DebugCommandActivator.OnDebugCommandInactivated += DebugCommandInactivated;
#endif
        }

        void IInGameViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(InGamePresenter), nameof(IInGameViewDelegate.OnViewDidUnload));

            _battlePauseController.Dispose();

            _appBackgroundPauseHandler?.Dispose();
            _menuPauseHandler?.Dispose();
            _gameEndPauseHandler?.Dispose();
            _battlePauseController.Dispose();
            _tutorialPauseHandler?.Dispose();
            _rushPauseHandler?.Dispose();
            _interruptAnimationPauseHandler?.Dispose();

            _viewPauseHandlerForTutorial?.Dispose();
            _viewPauseHandlerForRush?.Dispose();

            _rushAnimationCancellationTokenSource?.Cancel();
            _rushAnimationCancellationTokenSource?.Dispose();
            _rushAnimationCancellationTokenSource = null;

            _bossAppearanceAnimationCancellationTokenSource?.Cancel();
            _bossAppearanceAnimationCancellationTokenSource?.Dispose();
            _bossAppearanceAnimationCancellationTokenSource = null;

            _komaExpander.ResetKomaExpanding();

            SoundEffectLoader.Unload(SoundEffectTag.InGame);
            InitialAssetLoader.Unload();

#if GLOW_INGAME_DEBUG
            DebugCommandActivator.OnDebugCommandActivated -= DebugCommandActivated;
            DebugCommandActivator.OnDebugCommandInactivated -= DebugCommandInactivated;

            DisposeDebugCommandPauseHandler();
#endif
        }

        void IInGameViewDelegate.TransitToHome()
        {
            ResetTimeScale();
            SaveInGameOptionFinishedUseCase.SaveInGameOptionFinished(_playedSpecialAttackCutInUnitIds);
            TransitionToHome();
        }

        void IInGameViewDelegate.OnCharacterSummonButtonTapped(MasterDataId characterId)
        {
            SummonUserCharacterUseCase.SummonCharacter(BattleSide.Player, characterId);
        }

        void IInGameViewDelegate.OnUseSpecialAttackButtonTapped(MasterDataId characterId)
        {
            UseSpecialAttackUseCase.UseSpecialAttack(characterId);
        }

        /// <summary> 下部デッキにて、ロールがスペシャルのユニットをタップした際の召喚処理。 </summary>
        void IInGameViewDelegate.OnSpecialUnitSummonButtonTapped(MasterDataId characterId)
        {
            if (_isSpecialUnitSummonSelecting) return;

            var model = StartSpecialUnitSummonUseCase.StartSpecialUnitSummon(characterId);
            if (model.IsEmpty()) return;

            _isSpecialUnitSummonSelecting = true;
            _specialUnitSummonKomaRange = model.SpecialUnitSummonKomaRange;

            if (model.NeedTargetSelectTypeFlag)
            {
                // コマ選択を必要とする範囲を持つ必殺技であればコマ選択の表示に変更
                ViewController.StartSpecialUnitSummonTargetSelection(
                    characterId,
                    model.SpecialUnitSummonKomaRange,
                    model.KomaDictionary,
                    model.SummoningKomaIds);
            }

            // 必殺技の確認ダイアログを表示
            ShowSpecialUnitUseSkillConfirmationDialog(characterId, model.NeedTargetSelectTypeFlag);
            ViewController.StartSpecialUnitSkillConfirmationWindow();
        }

        void IInGameViewDelegate.OnUnitDetailLongPress(UserDataId userUnitId)
        {
            if (_isBattleOver) return;

            var args = new InGameUnitDetailViewController.Argument(userUnitId);
            var controller = ViewFactory.Create<InGameUnitDetailViewController, InGameUnitDetailViewController.Argument>(args);

            _inGameUnitDetailViewController = controller;
            _inGameUnitDetailViewController.OnClosed = () => _inGameUnitDetailViewController = null;

            // ユニット詳細チュートリアル中のフラグを設定する
            _inGameUnitDetailViewController.IsPlayingTutorial = _isPlayingUnitDetailTutorial;

            ViewController.PresentModally(_inGameUnitDetailViewController);
        }

        /// <summary> 総攻撃ボタンタップ時の挙動 </summary>
        void IInGameViewDelegate.OnRushButtonTapped()
        {
            ExecuteRushUseCase.ExecuteRush(BattleSide.Player);
        }

        bool IInGameViewDelegate.IsPlayingBattle()
        {
            return _isStarted && !_isBattleOver;
        }

        public void OnBattleSpeedButtonTapped()
        {
            var battleSpeed = ChangeBattleSpeedUseCase.ChangeBattleSpeed();

            ViewController.SetBattleSpeed(battleSpeed);
            SetupBattleSpeed(battleSpeed);
        }

        public void OnAutoButtonTapped()
        {
            // スペシャルキャラの召喚コマ選択中にAUTOをONにするときは、コマ選択を終了させる
            if (!_isAutoEnabled)
            {
                EndSpecialUnitSummon();
            }

            _isAutoEnabled = SwitchAutoUseCase.SwitchAuto();
            ViewController.OnAutoSwitched(_isAutoEnabled);
        }

        public bool SelectSpecialUnitSummonTarget(MasterDataId characterId, PageCoordV2 pos)
        {
            if (_battlePauseController.IsOn())
            {
                return false;
            }

            var res = SummonSpecialUnitUseCase.TryAddUnitSummonQueue(BattleSide.Player, characterId, pos);

            if (res)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }

            EndSpecialUnitSummon();
            return res;
        }

        public void FixedTick()
        {
            if (!_isStarted) return;
            if (_battlePauseController.IsOn()) return;

            UpdateBattleUseCase.Tick();

            PlayUnitTransformationAnimationIfNeeded();

            PlayInterruptAnimationIfNeeded();
        }

        public void OnSummonCharacterWithoutEffect(CharacterUnitModel characterUnitModel)
        {
            ViewController.OnSummonCharacterWithoutEffect(characterUnitModel);
        }

        public void OnSummonCharacter(CharacterUnitModel characterUnitModel)
        {
            ApplicationLog.Log(nameof(InGamePresenter), nameof(IBattlePresenter.OnSummonCharacter));

            if (characterUnitModel.IsBoss && !characterUnitModel.Transformation.IsTransformed())
            {
                PlayBossAppearanceAnimation(characterUnitModel);
                return;
            }

            ViewController.OnSummonCharacter(characterUnitModel, null);

            _unitTransformationAnimationDirector.RegisterTransformedUnitIfNeeded(characterUnitModel);
        }

        public void OnSummonSpecialUnit(SpecialUnitModel specialUnitModel, CoordinateRange coordinateRange)
        {
            ApplicationLog.Log(nameof(InGamePresenter), nameof(IBattlePresenter.OnSummonSpecialUnit));

            // 召喚実行
            DoAsync.Invoke(CancellationToken, async token =>
            {
                var summonTokenSource = new CancellationTokenSource();
                _specialUnitSummonCancellationTokenSources.Add(summonTokenSource);

                using var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                    CancellationToken,
                    summonTokenSource.Token);

                var interruptAnimation = new SpecialUnitSummonAnimation(
                    this,
                    ViewController,
                    _komaExpander,
                    ViewCoordinateConverter,
                    specialUnitModel,
                    coordinateRange,
                    CancellationToken);

                try
                {
                    await ViewController.OnSummonSpecialUnit(
                        specialUnitModel,
                        () =>
                        {
                            // バトル進行を止める演出の再生
                            _inGameInterruptAnimationDirector.Enqueue(interruptAnimation);
                        },
                        () =>
                        {
                            interruptAnimation.EndAnimation();
                        },
                        linkedCancellationTokenSource.Token);
                }
                finally
                {
                    _specialUnitSummonCancellationTokenSources.Remove(summonTokenSource);
                    summonTokenSource.Dispose();
                }
            });
        }

        public void OnGenerateGimmickObject(InGameGimmickObjectModel inGameGimmickObjectModel)
        {
            ViewController.OnGenerateGimmickObject(inGameGimmickObjectModel);
        }

        public void OnAppearAttack(IAttackModel attackModel)
        {
            ViewController.OnAppearAttack(attackModel);
        }

        public void OnUpdateAttacks(IReadOnlyList<IAttackModel> attackModels)
        {
            ViewController.OnUpdateAttacks(attackModels);
        }

        public void OnEndAttack(IAttackModel attackModel)
        {
            ViewController.OnEndAttack(attackModel);
        }

        public void OnUpdateDeck(
            IReadOnlyList<DeckUnitModel> deckUnitModels,
            BattlePoint currentCP)
        {
            // 変更影響が大きいのとスペシャルユニットに関係ないUseCaseでもDeckSpecialUnitSummonEvaluatorを使用させたくなかったため
            // 引数でなくこちらで取得する形に
            var canSummonAnySpecialUnit = DeckSpecialUnitSummonEvaluator.CanSummonBaseConditions(
                InGameScene.SpecialUnitSummonInfoModel,
                InGameScene.SpecialUnits,
                InGameScene.SpecialUnitSummonQueue,
                BattleSide.Player);

            IReadOnlyList<DeckUnitViewModel> viewModels = deckUnitModels
                .Select(model => model.ToViewModel(currentCP, canSummonAnySpecialUnit))
                .ToList();

            ViewController.OnUpdateDeck(viewModels);
        }

        public void OnUpdateFieldObjects(
            OutpostModel playerOutpostModel,
            OutpostModel enemyOutpostModel,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels,
            DefenseTargetModel defenseTarget,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults)
        {
            Profiler.BeginSample("OnUpdateFieldObjects.ViewController.OnUpdateFieldObjects");
            ViewController.OnUpdateFieldObjects(
                playerOutpostModel,
                enemyOutpostModel,
                characterUnitModels,
                specialUnitModels,
                defenseTarget,
                appliedAttackResults,
                _isDamageDisplay);
            Profiler.EndSample();

            Profiler.BeginSample("OnUpdateFieldObjects.UnitTransformationAnimationDirector.OnFieldObjectsUpdated");
            _unitTransformationAnimationDirector.OnFieldObjectsUpdated(characterUnitModels);
            Profiler.EndSample();

            Profiler.BeginSample("OnUpdateFieldObjects.PlaySpecialAttackCutInIfNeeded");
            PlaySpecialAttackCutInIfNeeded(characterUnitModels, specialUnitModels);
            Profiler.EndSample();

            Profiler.BeginSample("OnUpdateFieldObjects.ProcessKomaExpanding");
            _komaExpander.ExpandKomaIfNeeded(characterUnitModels);
            Profiler.EndSample();
        }


        public void OnUpdateBattlePoint(BattlePointModel battlePointModel)
        {
            ViewController.OnUpdatedBattlePoint(battlePointModel);
        }

        public void OnExecuteRush(
            RushModel rushModel,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            BattleSide battleSide,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<DeckUnitModel> deckUnitModels)
        {
            _rushAnimationCancellationTokenSource?.Cancel();
            _rushAnimationCancellationTokenSource?.Dispose();
            _rushAnimationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(CancellationToken);

            var playerUnitAssetKeys = characterUnitModels
                .Where(unit => unit.BattleSide == battleSide)
                .Select(unit => unit.AssetKey)
                .ToList();

            var specialUnitAssetKeys = deckUnitModels
                .Where(unit => unit.RoleType == CharacterUnitRoleType.Special)
                .Select(unit => unit.AssetKey)
                .ToList();

            DoAsync.Invoke(_rushAnimationCancellationTokenSource.Token, async cancellationToken =>
            {
                try
                {
                    _rushPauseHandler?.Dispose();
                    _rushPauseHandler = _battlePauseController.TurnOn();

                    await UniTask.WaitWhile(() => _isRushAnimationPlaying, cancellationToken: cancellationToken);
                    _isRushAnimationPlaying = true;

                    ViewController.EnablePageTapGuard();
                    ViewController.EnableHeaderTapGuard();

                    // 同じタイミングで召喚されるキャラなどもPauseさせるために待つ
                    await UniTask.Yield(PlayerLoopTiming.LastUpdate, cancellationToken: cancellationToken);

                    _viewPauseHandlerForRush?.Dispose();
                    _viewPauseHandlerForRush = ViewController.PauseBattleField();

                    await PlayRush(
                        playerUnitAssetKeys,
                        specialUnitAssetKeys,
                        rushModel.SpecialUnitBonus,
                        battleSide,
                        rushModel.ChargeCount,
                        calculatedRushAttackPower,
                        rushEvaluationType,
                        cancellationToken);
                }
                finally
                {
                    _rushAnimationCancellationTokenSource?.Dispose();
                    _rushAnimationCancellationTokenSource = null;

                    _isRushAnimationPlaying = false;
                }
            });
        }

        bool IsRushAnimationPlaying()
        {
            return _isRushAnimationPlaying;
        }

        public void OnUpdateRushGauge(RushModel rushModel)
        {
            ViewController.OnUpdateRush(rushModel);
        }

        public void OnUpdatePvpOpponentRushGauge(RushModel pvpOpponentRushModel)
        {
            ViewController.OnUpdateOpponentRush(pvpOpponentRushModel);
        }

        public void OnEffectBlocked(FieldObjectId fieldObjectId)
        {
            ViewController.OnEffectBlocked(fieldObjectId);
        }

        public void OnPlaceItems(IReadOnlyList<PlacedItemModel> newPlacedItemModels)
        {
            ViewController.OnPlaceItems(newPlacedItemModels);
        }

        public void OnRemoveConsumedItems(IReadOnlyList<PlacedItemModel> consumedItemModels)
        {
            ViewController.OnRemoveConsumedItems(consumedItemModels);
        }

        public void OnRemovePlacedItems(IReadOnlyList<PlacedItemModel> placedItemModels)
        {
            ViewController.OnRemovePlacedItems(placedItemModels);
        }

        public void OnSurvivedByGuts(FieldObjectId fieldObjectId)
        {
            ViewController.OnSurvivedByGuts(fieldObjectId);
        }

        public void OnDeckStateEffect(AppliedDeckStateEffectResultModel deckStateEffectResultModel)
        {
            switch (deckStateEffectResultModel.StateEffectType)
            {
                case StateEffectType.RushAttackPowerUp:
                    ViewController.OnRushAttackPowerUp(
                        deckStateEffectResultModel.TargetDeckBattleSide,
                        deckStateEffectResultModel.AttackerIds,
                        deckStateEffectResultModel.UpdatedParameter);
                    break;
                case StateEffectType.SpecialAttackCoolTimeShorten:
                case StateEffectType.SpecialAttackCoolTimeExtend:
                case StateEffectType.SummonCoolTimeShorten:
                case StateEffectType.SummonCoolTimeExtend:
                    // クールタイム変動効果
                    ViewController.OnCoolTimeVariation(
                        deckStateEffectResultModel.TargetDeckBattleSide,
                        deckStateEffectResultModel.StateEffectType,
                        deckStateEffectResultModel.TargetDeckCharacterId);
                    break;
                default:
                    break;
            }
        }

        public void OnGimmickObjectsRemoved(IReadOnlyList<InGameGimmickObjectModel> removedGimmickObjectModels)
        {
            ViewController.OnGimmickObjectsRemoved(removedGimmickObjectModels);
        }

        public void OnGimmickObjectTransformationStarted(InGameGimmickObjectModel gimmickObjectModel)
        {
            ViewController.OnGimmickObjectTransformationStarted(gimmickObjectModel);
        }

        public void OnUpdateKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            ViewController.OnUpdateKomas(komaDictionary);

            // スペシャルユニット選択中も同様に、射程内のコマが選択可能に変わったときに、射程の表示を更新
            // 合わせてプレイヤー側のスペシャルユニットが該当コマに存在するかでも判定
            if (_isSpecialUnitSummonSelecting)
            {
                var summoningKomaIds = InGameScene.SpecialUnits
                    .Where(unit => unit.BattleSide == BattleSide.Player)
                    .Select(unit => unit.LocatedKoma.Id)
                    .Distinct()
                    .ToList();
                ViewController.UpdateKomaSelectable(_specialUnitSummonKomaRange, komaDictionary, summoningKomaIds);
            }
        }

        public void OnResetKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            ViewController.OnResetKomas(komaDictionary);
        }

        public void OnBossAppearancePauseEnded()
        {
            _isWaitingBossAppearancePauseEnd = false;
        }

        public void OnMangaAnimationStart(IReadOnlyList<MangaAnimationModel> mangaAnimationModels)
        {
            _unitTransformationAnimationDirector.OnMangaAnimationStart(mangaAnimationModels);

            PlayMangaAnimation(mangaAnimationModels);
        }

        public void OnSpeak(UnitSpeechBalloonModel speechBalloonModel)
        {
            ViewController.OnSpeak(speechBalloonModel);
        }

        public void OnUpdateTimeLimit(StageTimeModel stageTimeModel)
        {
            ViewController.OnUpdateTimeLimit(stageTimeModel);
            ShowCountDown(stageTimeModel);
        }

        void ShowCountDown(StageTimeModel stageTimeModel)
        {
            if (!stageTimeModel.IsShowCountDown) return;

            for (int i = 0; i < _timeCountDowns.Count; i++)
            {
                var timeCountDown = _timeCountDowns[i];
                // 残り時間が節目の秒数を下回っている場合
                if (NeedShowCountDown(timeCountDown, stageTimeModel))
                {
                    // 「残りXX秒」の演出を表示
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_055);

                    ViewController.OnTimeCountDown(timeCountDown.Time);
                    _timeCountDowns[i] = timeCountDown with { HasBeenDisplayed = true };
                    break;
                }
            }
        }

        bool NeedShowCountDown(TimeCountDown timeCountDown, StageTimeModel stageTimeModel)
        {
            // もともとの制限時間が節目の秒数以下の場合は表示しない
            if (stageTimeModel.StageTimeLimit.ToSeconds() < timeCountDown.ToSecond()) return false;

            // 残り時間が下回っていて、まだ残り時間表示をしていない場合
            if (stageTimeModel.RemainingTime.ToSeconds() <= timeCountDown.ToSecond()
                && !timeCountDown.HasBeenDisplayed)
            {
                return true;
            }

            return false;
        }

        public void OnUpdateScore(
            InGameScore score,
            IReadOnlyList<ScoreCalculationResultModel> addedScoreModels,
            ScoreEffectVisibleFlag isScoreEffectVisible)
        {
            ViewController.OnUpdateScore(score, addedScoreModels, isScoreEffectVisible);
        }

        public void OnDefeatEnemy(
            DefeatEnemyCount defeatedCount,
            IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> defeatEnemyCountDictionary)
        {
            if (_battleEndConditions.OfType<DefeatedEnemyCountBattleEndConditionModel>().Any())
            {
                // 合計N体の敵撃破で勝利
                var conditionCount =
                    _battleEndConditions.OfType<DefeatedEnemyCountBattleEndConditionModel>().FirstOrDefault()
                        .DefeatedEnemyCount;
                ViewController.OnDefeatEnemy(StageEndConditionType.DefeatedEnemyCount, defeatedCount, conditionCount);
            }
            else if (_battleEndConditions.OfType<DefeatUnitBattleEndConditionModel>().Any())
            {
                // 特定IDの敵をN体撃破で勝利
                var condition =
                    _battleEndConditions.OfType<DefeatUnitBattleEndConditionModel>().FirstOrDefault();

                // ターゲットの敵を1度も倒していなかったら終了
                if (!defeatEnemyCountDictionary.ContainsKey(condition.CharacterId)) return;

                ViewController.OnDefeatEnemy(StageEndConditionType.DefeatUnit,
                    defeatEnemyCountDictionary[condition.CharacterId],
                    condition.DefeatEnemyCount);
            }
        }

        public void OnVictory(StageEndConditionType battleEndConditionType)
        {
            PreprocessBattleOver();

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                var victoryAnimationTask = UniTask.CompletedTask.AsAsyncUnitUniTask();
                try
                {
                    var victoryTask = VictoryUseCase.Victory(cancellationToken);
                    victoryAnimationTask = UniTask.Create(async () =>
                    {
                        // 総攻撃演出中は待つ
                        await UniTask.WaitUntil(() => !IsRushAnimationPlaying(), cancellationToken: cancellationToken);

                        var preVictoryModel = PreVictoryUseCase.PreVictory();
                        if (!preVictoryModel.MangaAnimationAssetKey.IsEmpty())
                        {
                            await ShowMangaAnimation(
                                cancellationToken,
                                preVictoryModel.MangaAnimationAssetKey,
                                preVictoryModel.AnimationSpeed);
                        }

                        ViewController.OnVictory();

                        // 満たした終了条件に合わせた演出表示
                        await PlayBattleEndAnimation(
                            StageEndType.Victory,
                            battleEndConditionType,
                            cancellationToken);

                        await ShowVictoryAnimationView(cancellationToken);
                    }).AsAsyncUnitUniTask().Preserve();

                    var (victoryResultModel, _) = await UniTask.WhenAll(victoryTask, victoryAnimationTask);

                    await ViewController.ShowBlackCurtain(cancellationToken);

                    var isAdChallenge = victoryResultModel.InGameRetryModel.IsAdChallenge;
                    var (isRetry, staminaBoostCount) = await ShowVictoryResultViewAndGetRetryInfo(victoryResultModel, cancellationToken);

                    // バトル後にチュートリアル再生する場は遷移をここでせず、終了フラグを立てる
                    if (TutorialTransitionSkipUseCase.IsSkipTransition())
                    {
                        IsEndTutorial = true;
                        return;
                    }

                    await HandleResultTransitionAsync(cancellationToken, staminaBoostCount, isRetry, isAdChallenge);
                }
                catch (Exception e) when (
                    e is QuestPeriodOutsideException
                        or EventPeriodOutsideException)
                {
                    // クエストやイベントの開催期間が終了している場合は
                    // セッションを破棄した上でホームに遷移
                    await HandlePeriodOutsideAnimationAndException(
                        victoryAnimationTask,
                        e,
                        cancellationToken);
                }
            });
        }

        public void OnDefeatWithContinue(
            StageEndConditionType battleEndConditionType)
        {
            PreprocessBattleOver();
            ViewController.IsDeadAnimation = false;

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                ViewController.OnDefeat();

                // 満たした終了条件に合わせた演出表示
                await PlayBattleEndAnimation(
                    StageEndType.Defeat,
                    battleEndConditionType,
                    cancellationToken);

                await ViewController.ShowBlackCurtain(1f, cancellationToken);

                var isContinue = false;
                var continueConfirmationResult = await ConfirmContinue(cancellationToken);
                switch (continueConfirmationResult)
                {
                    case ContinueConfirmationResult.Continue:
                        // コンティニューが選択されたらバトルを再開する
                        // ContinueUseCaseはContinuePresenterで処理されてる状態
                        EndSpecialUnitSummon();
                        ViewController.RecoverPlayerOutpost();

                        await UniTask.Delay(500, cancellationToken: cancellationToken);
                        await ViewController.HideBlackCurtain(cancellationToken);

                        await UniTask.Delay(500, cancellationToken: cancellationToken);

                        SetupBattleSpeed(_battleSpeed);

                        ViewController.DisableTapGuard();
                        ViewController.IsDeadAnimation = true;

                        _gameEndPauseHandler?.Dispose();
                        _gameEndPauseHandler = null;

                        _isBattleOver = false;
                        break;
                    case ContinueConfirmationResult.Cancel:
                        // 敗北確定
                        var resultModel = await DefeatUseCase.Defeat(cancellationToken);

                        var (isRetry, staminaBoostCount) = await ShowDefeatResultViewAndGetRetryInfo(resultModel, cancellationToken);
                        var isAdChallenge = resultModel.InGameRetryModel.IsAdChallenge;

                        var model = CheckContentOpenUseCase.CheckContentOpenStatus();
                        if (model.IsInGameStageValid == InGameStageValidFlag.False)
                        {
                            PeriodOutsideExceptionWireframe.ShowPeriodOutsideExceptionMessage(model, TransitionToHome);
                            return;
                        }

                        await HandleResultTransitionAsync(cancellationToken, staminaBoostCount, isRetry, isAdChallenge);

                        break;
                    case ContinueConfirmationResult.QuestPeriodOutside:
                        // 敗北確定。期間外時はView側の敗北表示などは行わない
                        await DefeatUseCase.Defeat(cancellationToken);
                        TransitionToHome();
                        break;
                }
            });
        }

        public void OnDefeatCannotContinue(
            StageEndConditionType battleEndConditionType)
        {
            PreprocessBattleOver();
            ViewController.IsDeadAnimation = false;

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                var defeatAnimationTask = UniTask.Create(async () =>
                {
                    ViewController.OnDefeat();

                    // 満たした終了条件に合わせた演出表示
                    await PlayBattleEndAnimation(
                        StageEndType.Defeat,
                        battleEndConditionType,
                        cancellationToken);

                    await ViewController.ShowBlackCurtain(1f, cancellationToken);
                }).AsAsyncUnitUniTask().Preserve();

                // 敗北確定
                var defeatTask = DefeatUseCase.Defeat(cancellationToken);

                var (resultModel, _) = await UniTask.WhenAll(defeatTask, defeatAnimationTask);

                var (isRetry, staminaBoostCount) = await ShowDefeatResultViewAndGetRetryInfo(resultModel, cancellationToken);
                var isAdChallenge = resultModel.InGameRetryModel.IsAdChallenge;

                var model = CheckContentOpenUseCase.CheckContentOpenStatus();
                if (model.IsInGameStageValid == InGameStageValidFlag.False)
                {
                    PeriodOutsideExceptionWireframe.ShowPeriodOutsideExceptionMessage(model, TransitionToHome);
                    return;
                }

                await HandleResultTransitionAsync(cancellationToken, staminaBoostCount, isRetry, isAdChallenge);
            });
        }

        public void OnFinish(StageEndConditionType battleEndConditionType)
        {
            PreprocessBattleOver();

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                UniTask<AsyncUnit> finishAnimationTask = UniTask.CompletedTask.AsAsyncUnitUniTask();
                try
                {
                    var victoryTask = VictoryUseCase.Victory(cancellationToken);
                    finishAnimationTask = UniTask.Create(async () =>
                    {
                        // マンガ演出があれば再生
                        var preFinishModel = PreFinishUseCase.PreFinish();
                        if (!preFinishModel.MangaAnimationAssetKey.IsEmpty())
                        {
                            await ShowMangaAnimation(
                                cancellationToken,
                                preFinishModel.MangaAnimationAssetKey,
                                preFinishModel.AnimationSpeed);
                        }

                        ViewController.OnFinish();

                        // 満たした終了条件に合わせた演出表示
                        await PlayBattleEndAnimation(
                            StageEndType.Finish,
                            battleEndConditionType,
                            cancellationToken);

                        await ShowFinishAnimationView(preFinishModel.PvpResultModel, cancellationToken);
                    }).AsAsyncUnitUniTask().Preserve(); // catchでawaitするためPreserve

                    var (victoryResultModel, _) = await UniTask.WhenAll(victoryTask, finishAnimationTask);

                    // 降臨バトルの場合は残り挑戦回数のローカル通知を更新
                    if (victoryResultModel.InGameType == InGameType.AdventBattle)
                    {
                        LocalNotificationScheduler.RefreshRemainAdventBattleCountSchedule();
                    }

                    await ViewController.ShowBlackCurtain(cancellationToken);

                    var isRetry = false;
                    var staminaBoostCount = StaminaBoostCount.One;
                    var isAdChallenge = victoryResultModel.InGameRetryModel.IsAdChallenge;

                    switch (victoryResultModel.InGameType)
                    {
                        case InGameType.AdventBattle:
                            (isRetry, staminaBoostCount) = await ShowAdventBattleResultViewAndGetRetryInfo(victoryResultModel, cancellationToken);
                            break;
                        case InGameType.Pvp:
                            await ShowPvpResultView(victoryResultModel, cancellationToken);
                            break;
                        case InGameType.Normal:
                            (isRetry, staminaBoostCount) = await ShowFinishResultViewAndGetRetryInfo(victoryResultModel, cancellationToken);
                            break;
                    }

                    await HandleResultTransitionAsync(cancellationToken, staminaBoostCount, isRetry, isAdChallenge);
                }
                catch (Exception e) when (
                    e is QuestPeriodOutsideException
                        or EventPeriodOutsideException
                        or PvpPeriodOutsideException
                        or AdventBattlePeriodOutsideException)
                {
                    // クエストやイベント、Pvp、降臨バトルの開催期間が終了している場合は
                    // セッションを破棄した上でホームに遷移
                    await HandlePeriodOutsideAnimationAndException(
                        finishAnimationTask,
                        e,
                        cancellationToken);
                }
            });
        }

        void PreprocessBattleOver()
        {
            _isBattleOver = true;

            // 各キャンセルにてViewController.DisableTapGuardが呼ばれているため先にここで呼び出し
            CancelAllAnimations();

            ViewController.EnableTapGuard();
            EndSpecialUnitSummon();

            _inGameUnitDetailViewController?.Close();

            SaveInGameOptionFinishedUseCase.SaveInGameOptionFinished(_playedSpecialAttackCutInUnitIds);

            _gameEndPauseHandler?.Dispose();
            _gameEndPauseHandler = _battlePauseController.TurnOn();

            ResetTimeScale();
        }

        /// <summary> バトル終了時に満たした終了条件に合わせて演出を表示 </summary>
        async UniTask PlayBattleEndAnimation(
            StageEndType stageEndType,
            StageEndConditionType battleEndConditionType,
            CancellationToken cancellationToken)
        {

            switch (battleEndConditionType)
            {
                // 敵ゲート爆破演出
                case StageEndConditionType.EnemyOutpostBreakDown:
                    ViewController.EnemyOutpostBroken();

                    // 爆破演出待ち
                    await UniTask.Delay(1500, cancellationToken: cancellationToken);
                    break;

                // 味方ゲート爆破演出
                case StageEndConditionType.PlayerOutpostBreakDown:
                    ViewController.PlayerOutpostBroken();

                    // 爆破演出待ち
                    await UniTask.Delay(1500, cancellationToken: cancellationToken);
                    break;

                // オブジェクト防衛失敗時のダイアログ表示
                case StageEndConditionType.DefenseTargetBreakDown:
                    if (stageEndType == StageEndType.Defeat)
                    {
                        // 防衛オブジェクトにスクロールして状態を見せる
                        if (ViewController.ActualView.IsScrollablePage)
                        {
                            var scrollPos = ViewController.ActualView.GetDefenseTargetViewPos();
                            await ViewController.ScrollPage(
                                scrollPos,
                                1.0f,
                                cancellationToken,
                                isIndependentUpdate: true);
                        }

                        await UniTask.Delay(1000, cancellationToken: cancellationToken);

                        await ShowDefeatDialogView(battleEndConditionType, cancellationToken);
                    }

                    break;
            }
        }

        async UniTask ShowMangaAnimation(
            CancellationToken cancellationToken,
            MangaAnimationAssetKey mangaAnimationAssetKey,
            MangaAnimationSpeed animationSpeed)
        {
            _gameEndMangaAnimationCancellationTokenSource = new CancellationTokenSource();

            var linkedCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _gameEndMangaAnimationCancellationTokenSource.Token).Token;

            ViewController.ShowTapToSkip(0.5f);

            await ViewController
                .PlayMangaAnimation(mangaAnimationAssetKey, animationSpeed, linkedCancellationToken)
                .SuppressCancellationThrow();

            _gameEndMangaAnimationCancellationTokenSource?.Dispose();
            _gameEndMangaAnimationCancellationTokenSource = null;

            ViewController.HideTapToSkip();

            cancellationToken.ThrowIfCancellationRequested();
        }

        async UniTask HandlePeriodOutsideAnimationAndException(
            UniTask animationTask,
            Exception exception,
            CancellationToken cancellationToken)
        {
            await AbortUseCase.Abort(cancellationToken);

            // アニメーション演出は終了待ちにする
            if (!animationTask.Status.IsCompleted())
            {
                await UniTask.WaitUntil(
                    () => animationTask.Status.IsCompleted(),
                    cancellationToken: cancellationToken);
            }

            // クエストやイベントの開催期間が終了している場合はホームに遷移
            PeriodOutsideExceptionWireframe.ShowPeriodOutsideExceptionMessage(
                exception,
                TransitionToHome);
        }

        void IInGameViewDelegate.OnMenuButtonTapped()
        {
            ShowMenu();
        }

        void IInGameViewDelegate.OnSkipButtonTapped()
        {
            CancelBattleStartScroll();
            CancelBattleStartMangaAnimation();
            CancelBattleEndMangaAnimation();
            CancelDefenseTargetHighlight();
            CancelPlayerOutpostHpHighlight();
            CancelBattleStartNoiseAnimation();

            _inGameInterruptAnimationDirector.SkipCurrentAnimation();
        }

        void IInGameViewDelegate.OnEscapeButtonTapped()
        {
            bool isCanceled = CancelBattleStartScroll();
            isCanceled |= CancelBattleStartMangaAnimation();
            isCanceled |= CancelBattleEndMangaAnimation();
            isCanceled |= CancelDefenseTargetHighlight();
            isCanceled |= CancelPlayerOutpostHpHighlight();
            isCanceled |= CancelBattleStartNoiseAnimation();
            isCanceled |= _inGameInterruptAnimationDirector.SkipCurrentAnimation();

            if (isCanceled)
            {
                return;
            }

            if (InGameScene.IsBattleOver || InGameScene.IsBattleGiveUp)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            ShowMenu();
        }

        void IInGameViewDelegate.PresentModally(UIViewController controller, bool animated, Action completion)
        {
            ViewController.PresentModally(controller, animated, completion);
        }

        void IInGamePauseControl.Pause()
        {
            ShowPause();
        }

        void IInGameMenuSettingUpdateControl.SwitchDeckLayout()
        {
            ChangeDeckMode();
        }

        void IInGameMenuSettingUpdateControl.SetSpecialAttackCutInPlayType(
            SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            // モードの切り替えだけを行う
            _specialAttackCutInPlayType = specialAttackCutInPlayType;
        }

        void IInGameMenuSettingUpdateControl.SetDamageDisplay(DamageDisplayFlag isDamageDisplay)
        {
            _isDamageDisplay = isDamageDisplay;
            SetDamageDisplay(isDamageDisplay);
        }

        void ShowMenu()
        {
            if (_shownMenu != null) return;
            if (InGameScene.IsBattleOver || InGameScene.IsBattleGiveUp) return;

            _menuPauseHandler?.Dispose();
            _menuPauseHandler = _battlePauseController.TurnOn();

            var timeScaleControlHandler = TimeScaleController.ChangeTimeScale(
                0f,
                TimeScaleType.Fixed,
                TimeScalePriorityDefinitions.Menu);

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

            var controller = ViewFactory.Create<InGameMenuViewController, InGameMenuViewController.Argument>(
                new InGameMenuViewController.Argument(
                    timeScaleControlHandler,
                    _ =>
                    {
                        _menuPauseHandler?.Dispose();
                        _menuPauseHandler = null;

                        timeScaleControlHandler.Dispose();

                        _shownMenu = null;
                    }));
            _shownMenu = controller;
            ViewController.PresentModally(controller, false);
        }

        void ShowPause()
        {
            if (!_isStarted) return;
            if (_shownPause != null) return;
            if (InGameScene.IsBattleOver) return;
            if (InGameScene.IsBattleGiveUp ) return;
            if (_isPlayingTutorial) return;

            _appBackgroundPauseHandler?.Dispose();
            _appBackgroundPauseHandler = _battlePauseController.TurnOn();

            var timeScaleControlHandler = TimeScaleController.ChangeTimeScale(
                0f,
                TimeScaleType.Fixed,
                TimeScalePriorityDefinitions.BackGroundPause);

            var controller = ViewFactory.Create<InGamePauseViewController, InGamePauseViewController.Argument>(
                new InGamePauseViewController.Argument(_ =>
                {
                    _appBackgroundPauseHandler?.Dispose();
                    _appBackgroundPauseHandler = null;

                    timeScaleControlHandler.Dispose();

                    _shownPause = null;
                }));
            _shownPause = controller;

            ViewController.PresentModally(controller, animated: false);
        }

        /// <summary>
        /// ボス登場演出
        /// </summary>
        void PlayBossAppearanceAnimation(CharacterUnitModel bossUnitModel)
        {
            // バトル進行を止めての演出
            var animation = new BossAppearanceAnimation(
                ViewController,
                bossUnitModel,
                ViewCoordinateConverter,
                BackgroundMusicPlayable,
                _bossBGMAssetKey);

            _inGameInterruptAnimationDirector.Enqueue(animation);

            // バトル進行再開後の演出
            _bossAppearanceAnimationCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(CancellationToken);

            DoAsync.Invoke(_bossAppearanceAnimationCancellationTokenSource.Token, async cancellationToken =>
            {
                MultipleSwitchHandler viewPauseHandler = null;

                try
                {
                    // バトル進行を止めた演出が終わるのを待つ
                    await UniTask.WaitUntil(
                        () => !_inGameInterruptAnimationDirector.IsPlaying,
                        cancellationToken: cancellationToken);

                    // Domain側でのボス登場演出終了までTapGuardとViewのポーズ
                    ViewController.EnableTapGuard();
                    viewPauseHandler = ViewController.PauseWithoutDarknessClearAndPlayerUnit();

                    await UniTask.WaitUntil(
                        () => !_isWaitingBossAppearancePauseEnd,
                        cancellationToken: cancellationToken);

                    viewPauseHandler?.Dispose();
                    viewPauseHandler = null;

                    ViewController.DisableTapGuard();

                    // コマの拡大を戻す
                    await ViewController.ScalePage(1f, 0.1f, cancellationToken);
                }
                finally
                {
                    viewPauseHandler?.Dispose();

                    if (ViewController.View != null)
                    {
                        ViewController.DisableTapGuard();
                        ViewController.ResetPageScale();
                    }
                }
            });
        }

        void PlaySpecialAttackCutInIfNeeded(
            IReadOnlyList<CharacterUnitModel> unitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels)
        {
            var cutInInfoList = GetCutInInfoList(unitModels, specialUnitModels);
            if (cutInInfoList.Count == 0) return;

            // カットインアニメーションをキューに追加
            foreach (var cutInInfo in cutInInfoList)
            {
                var animation = new SpecialAttackCutInAnimation(
                    ViewController,
                    cutInInfo.Id,
                    cutInInfo.Color,
                    cutInInfo.AssetKey,
                    cutInInfo.UnitAttackViewInfo,
                    cutInInfo.SpecialAttackCutInSelfPauseFlag);

                _inGameInterruptAnimationDirector.Enqueue(animation);
            }
        }

        List<CutInInfo> GetCutInInfoList(
            IReadOnlyList<CharacterUnitModel> unitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels)
        {
            var cutInInfoList = new List<CutInInfo>();

            // 通常の味方ユニット
            foreach (var unitModel in unitModels)
            {
                if (unitModel.BattleSide != BattleSide.Player) continue;
                if (!unitModel.IsStateStart(UnitActionState.PreSpecialAttack)) continue;

                var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(unitModel.AssetKey);
                if (unitAttackViewInfoSet == null) continue;

                var attackViewInfo = unitAttackViewInfoSet.SpecialAttackViewInfo;
                if (attackViewInfo.CutInPrefab_background == null && attackViewInfo.CutInPrefab_unitEffect == null) continue;

                if (CheckSpecialAttackCutInSkipAndUpdateIdList(unitModel.CharacterId)) continue;

                var cutInInfo = new CutInInfo(
                    unitModel.Id,
                    unitModel.Color,
                    unitModel.AssetKey,
                    attackViewInfo,
                    SpecialAttackCutInSelfPauseFlag.False);
                cutInInfoList.Add(cutInInfo);
            }

            // ロールがスペシャルのキャラ
            foreach (var specialUnitModel in specialUnitModels)
            {
                if (specialUnitModel.BattleSide != BattleSide.Player) continue;
                if (!specialUnitModel.SpecialUnitUseSpecialAttackFlag) continue;

                var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(specialUnitModel.AssetKey);
                if (unitAttackViewInfoSet == null) continue;

                var attackViewInfo = unitAttackViewInfoSet.SpecialAttackViewInfo;
                if (attackViewInfo.CutInPrefab_background == null && attackViewInfo.CutInPrefab_unitEffect == null) continue;

                if (CheckSpecialAttackCutInSkipAndUpdateIdList(specialUnitModel.CharacterId)) continue;

                var cutInInfo = new CutInInfo(
                    specialUnitModel.Id,
                    specialUnitModel.Color,
                    specialUnitModel.AssetKey,
                    attackViewInfo,
                    SpecialAttackCutInSelfPauseFlag.True);
                cutInInfoList.Add(cutInInfo);
            }

            return cutInInfoList;
        }


        async UniTask PlayRush(
            IReadOnlyList<UnitAssetKey> unitAssetKeys,
            IReadOnlyList<UnitAssetKey> specialUnitAssetKey,
            PercentageM specialUnitBonus,
            BattleSide battleSide,
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            CancellationToken cancellationToken)
        {
            // ポーズの解除は任意のタイミングで行うため、Actionで解除処理を渡す
            var unpauseAction = new Action(() =>
            {
                _viewPauseHandlerForRush?.Dispose();
                _viewPauseHandlerForRush = null;

                _rushPauseHandler?.Dispose();
                _rushPauseHandler = null;

                if (ViewController != null)
                {
                    ViewController.DisablePageTapGuard();
                    ViewController.DisableHeaderTapGuard();
                    ViewController.DisableTapGuard();
                }
            });

            // 演出
            ViewController.EnableTapGuard();
            await ViewController.PlayRush(
                unitAssetKeys,
                specialUnitAssetKey,
                specialUnitBonus,
                unpauseAction,
                battleSide,
                chargeCount,
                calculatedRushAttackPower,
                rushEvaluationType,
                cancellationToken);
        }



        void PlayMangaAnimation(IReadOnlyList<MangaAnimationModel> mangaAnimationModels)
        {
            var mangaAnimationModelsForPlay = mangaAnimationModels
                .Where(model => model.NeedsImmediatePlay())
                .ToList();

            if (mangaAnimationModelsForPlay.Count == 0) return;

            // Pauseさせない演出を再生させる
            foreach (var mangaAnimationModel in mangaAnimationModelsForPlay.Where(model => !model.IsPause))
            {
                ViewController
                    .PlayMangaAnimation(
                        mangaAnimationModel.AssetKey,
                        mangaAnimationModel.AnimationSpeed,
                        CancellationToken)
                    .Forget();
            }

            // Pauseさせる演出をキューに追加
            var interruptMangaAnimations = mangaAnimationModelsForPlay.Where(model => model.IsPause).ToList();
            if (interruptMangaAnimations.Count == 0) return;

            foreach (var mangaAnimationModel in interruptMangaAnimations)
            {
                var animation = new InterruptMangaAnimation(
                    ViewController,
                    mangaAnimationModel.AssetKey,
                    mangaAnimationModel.AnimationSpeed,
                    mangaAnimationModel.CanSkip);

                _inGameInterruptAnimationDirector.Enqueue(animation);
            }
        }

        void PlayUnitTransformationAnimationIfNeeded()
        {
            if (_isBattleOver) return;

            var animationInfos = _unitTransformationAnimationDirector.GetAnimationInfosThatCanBeStarted();
            if (animationInfos.Count == 0) return;

            // 変身演出をキューに追加
            foreach (var info in animationInfos)
            {
                var animation = new UnitTransformationAnimation(ViewController, info);
                _inGameInterruptAnimationDirector.Enqueue(animation);
            }

            _unitTransformationAnimationDirector.RemoveAnimationInfosThatCanBeStarted();
        }



        void PlayInterruptAnimationIfNeeded()
        {
            if (_inGameInterruptAnimationDirector.IsPlaying) return;
            if (!_inGameInterruptAnimationDirector.HasAnimation) return;
            if (_isBattleOver) return;
            if (_isInterruptAnimationPlaying) return;

            if (_isRushAnimationPlaying)
            {
                // 総攻撃演出の場合は、総攻撃の演出を途中キャンセルする
                _rushAnimationCancellationTokenSource?.Cancel();
                _rushAnimationCancellationTokenSource?.Dispose();
                _rushAnimationCancellationTokenSource = null;
            }

            _isInterruptAnimationPlaying = true;

            // バトル進行を停止
            _interruptAnimationPauseHandler?.Dispose();
            _interruptAnimationPauseHandler = _battlePauseController.TurnOn();

            // 画面全体のタップガード
            ViewController.EnableTapGuard();

            // スペシャルキャラの召喚コマ選択を終了
            EndSpecialUnitSummon();

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                try
                {
                    await _inGameInterruptAnimationDirector.PlayAnimation(cancellationToken);
                }
                finally
                {
                    // タップガードを解除
                    if (ViewController != null)
                    {
                        ViewController.DisableTapGuard();
                    }

                    _interruptAnimationPauseHandler?.Dispose();
                    _interruptAnimationPauseHandler = null;

                    _isInterruptAnimationPlaying = false;
                }
            });
        }

        void CancelAllAnimations()
        {
            CancelSummonSpecialUnit();

            _inGameInterruptAnimationDirector?.Cancel();

            _bossAppearanceAnimationCancellationTokenSource?.Cancel();
            _bossAppearanceAnimationCancellationTokenSource?.Dispose();
            _bossAppearanceAnimationCancellationTokenSource = null;

            _komaExpander.ResetKomaExpanding();
        }

        void TransitionToHome()
        {
            SceneNavigation.Switch<HomeTopTransition>(default, SceneInBuildName.HOME).Forget();
        }

        async UniTask HandleResultTransitionAsync(
            CancellationToken cancellationToken,
            StaminaBoostCount staminaBoostCount,
            bool isRetry,
            AdChallengeFlag isAdChallenge)
        {
            if (isRetry)
            {
                // 遷移前にBGMを停止
                BackgroundMusicPlayable.Stop();

                await InGameRetrySceneNavigator.RetryStage(cancellationToken, staminaBoostCount, isAdChallenge);
            }
            else
            {
                TransitionToHome();
            }
        }

        async UniTask<(bool isRetry, StaminaBoostCount staminaBoostCount)> RetryButtonAction(CancellationToken cancellationToken)
        {
            // NOTE: 挑戦回数や挑戦可能かはボタン表示でチェック済み
            // スタミナを消費するNormalステージの場合のみ使用する

            // スタミナブースト利用可能か
            var useCaseModel = InGameRetryStaminaBoostUseCase.IsStaminaBoostAvailable();
            if (useCaseModel.IsStaminaBoostAvailable)
            {
                // スタミナブーストダイアログを表示
                var isRetry = false;
                var staminaBoostCount = StaminaBoostCount.One;
                var argument = new StaminaBoostDialogViewController.Argument(useCaseModel.StageId);
                var controller = ViewFactory.Create<StaminaBoostDialogViewController, StaminaBoostDialogViewController.Argument>(argument);
                controller.OnStartButtonTappedAction = (receivedHasEnoughStamina, receivedStaminaBoostCount) =>
                {
                    staminaBoostCount = receivedStaminaBoostCount;
                    if (!receivedHasEnoughStamina)
                    {
                        // スタミナが足りない場合はスタミナ購入画面を表示
                        ShowStaminaRecoverDialog(() =>
                        {
                            // スタミナブーストダイアログを更新
                            controller.BeginAppearanceTransition(true, false);
                        });
                    }
                    else
                    {
                        // スタミナが足りている場合はリトライフラグを立てる
                        isRetry = true;
                    }

                };
                ViewController.PresentModally(controller);

                // ダイアログが閉じられるか、スタートするまで待機
                await UniTask.WaitUntil(() => isRetry || controller.IsBeingDismissed || !controller.IsViewLoaded, cancellationToken: cancellationToken);

                // スタミナが足りているかに応じてフラグとスタミナブーストカウントを返す
                return (isRetry, staminaBoostCount);
            }
            else if (useCaseModel.IsEnoughStamina)
            {
                // スタミナが足りている場合
                return (true, StaminaBoostCount.One);
            }
            else
            {
                // スタミナブーストがない場合にスタミナが足りないため購入画面を表示
                HandleStaminaShortage(false);
                return (false, StaminaBoostCount.One);
            }
        }

        void HandleStaminaShortage(bool hasEnoughStamina)
        {
            // スタミナが足りている場合は何もしない
            if (hasEnoughStamina) return;

            // スタミナが足りない場合はスタミナ購入画面へ遷移
            ShowStaminaRecoverDialog();
        }

        void ShowStaminaRecoverDialog(Action onDismissAction = null)
        {
            if(_staminaRecoverySelectViewController != null)
            {
                // 既に表示中の場合は何もしない
                return;
            }

            var argument = new StaminaRecoverySelectViewController.Argument(StaminaShortageFlag.True);
            var controller = ViewFactory.Create<StaminaRecoverySelectViewController,
                StaminaRecoverySelectViewController.Argument>(argument);
            _staminaRecoverySelectViewController = controller;

            controller.OnDismissAction = () =>
            {
                _staminaRecoverySelectViewController = null;
                onDismissAction?.Invoke();
            };

            ViewController.PresentModally(controller);
        }

        void SetupBattleSpeed(BattleSpeed battleSpeed)
        {
            _battleSpeed = battleSpeed;

            var speed = battleSpeed switch
            {
                BattleSpeed.x1 => 1.0f,
                BattleSpeed.x1_5 => 1.5f,
                BattleSpeed.x2 => 2.0f,
                BattleSpeed.x3 => 3.0f,
                _ => 1.0f
            };

            _battleSpeedTimeScaleControlHandler?.Dispose();
            _battleSpeedTimeScaleControlHandler = TimeScaleController.ChangeTimeScale(
                speed,
                TimeScaleType.Multiply,
                TimeScalePriorityDefinitions.BattleSpeed);
        }

        /// <summary> バトル開始時の演出群 </summary>
        async UniTask PlayPreStartAnimation(InitializeResultModel initializeResult, CancellationToken cancellationToken)
        {
            // 各演出が必要か
            var needsPageScroll = ViewController.ActualView.IsScrollablePage;
            var needsMangaAnimation = !initializeResult.StartMangaAnimationAssetKey.IsEmpty();
            var needsBattleStartNoiseAnimation = initializeResult.NeedsBattleStartNoiseAnimation;
            var needsPlayerOutpostHpHighlight = initializeResult.PlayerOutpost.OutpostHpSpecialRuleFlag;
            var needsDefenseTargetHighlight = !initializeResult.DefenseTargetModel.IsEmpty();

            var needsPreStartAnimation = needsPageScroll
                                         || needsMangaAnimation
                                         || needsBattleStartNoiseAnimation
                                         || needsPlayerOutpostHpHighlight
                                         || needsDefenseTargetHighlight;

            // 暗転とスキップ表示
            if (needsPreStartAnimation)
            {
                ViewController.SetUnitConditionVisible(false); // HPゲージとかを非表示
                ViewController.ShowTapToStartBattle();
                await ViewController.ShowIndividualBlackCurtain(cancellationToken);
            }

            // ページ全体が画面表示サイズより縦長の場合スクロールがオンになる。その場合ページ全体を写すようにスクロールを行う
            if (needsPageScroll)
            {
                await PlayBattleStartScroll();
            }

            // 原画演出
            var isMangaAnimationSkipped = false;
            if (needsMangaAnimation)
            {
                _battleStartMangaAnimationCancellationTokenSource = new CancellationTokenSource();

                var linkedCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, _battleStartMangaAnimationCancellationTokenSource.Token).Token;

                isMangaAnimationSkipped = await ViewController
                    .PlayMangaAnimation(
                        initializeResult.StartMangaAnimationAssetKey,
                        initializeResult.StartMangaAnimationSpeed,
                        linkedCancellationToken)
                    .SuppressCancellationThrow();

                _battleStartMangaAnimationCancellationTokenSource?.Dispose();
                _battleStartMangaAnimationCancellationTokenSource = null;

                cancellationToken.ThrowIfCancellationRequested();
            }

            // ノイズ演出
            if (needsBattleStartNoiseAnimation && !isMangaAnimationSkipped)
            {
                await PlayBattleStartNoiseAnimation(cancellationToken);
            }

            // 味方ゲートのHPが特別ルールによって設定されている場合、HPを強調表示
            if (needsPlayerOutpostHpHighlight)
            {
                await PlayPlayerOutpostHpHighlight();
            }

            // 防衛オブジェクト設定時に強調表示
            if (needsDefenseTargetHighlight)
            {
                await PlayDefenseTargetHighlight(
                    initializeResult.DefenseTargetModel,
                    ViewController.ActualView.IsScrollablePage,
                    cancellationToken);
            }

            // 暗転とスキップ表示を消す
            if (needsPreStartAnimation)
            {
                ViewController.SetUnitConditionVisible(true);
                ViewController.HideTapToStartBattle();
                await ViewController.HideIndividualBlackCurtain(cancellationToken);
            }
        }

        /// <summary> バトル開始時のスクロール演出 </summary>
        async UniTask PlayBattleStartScroll()
        {
            ViewController.SetUnrestrictedScroll(); // スクロールの弾性による干渉を抑えるため一時的にUnrestrictedに変更

            _battleStartScrollCancellationTokenSource = new CancellationTokenSource();
            var linkedToken = CancellationTokenSource.CreateLinkedTokenSource(CancellationToken,
                _battleStartScrollCancellationTokenSource.Token).Token;

            try
            {
                ViewController.HideBattleFieldBlackCurtain();

                // 開始時の下端だけは敵ゲートのコマを画面中央に表示した状態から開始するように即時移動
                var enemyOutpostFieldViewCoord = ViewController.GetEnemyOutpostViewPos();
                await ViewController.ScrollPage(
                    enemyOutpostFieldViewCoord,
                    0,
                    linkedToken,
                    isUnrestricted: true);

                await UniTask.Delay(500, cancellationToken: linkedToken);

                // 倍速ボタンによるTimeScale変更に影響されないようisIndependentUpdateをtrueに
                // 異なるPageHeightでも同じ移動速度になるようスクロール距離に合わせて移動時間を算出。
                // スクロール上端(PosYが0)から敵ゲートのコマ中心までの距離
                var distance = Mathf.Abs(ViewController.GetEnemyOutpostKomaSetCenterPos().y);
                var scrollDuration = distance * ViewController.ActualView.BattleStartScrollDurationFor100Pixels / 100;
                await ViewController.ScrollPage(
                    1.0f,
                    scrollDuration,
                    linkedToken,
                    isIndependentUpdate: true);

                await UniTask.Delay(300, cancellationToken: linkedToken);
                _battleStartScrollCancellationTokenSource.Dispose();
                _battleStartScrollCancellationTokenSource = null;
            }
            catch (OperationCanceledException)
            {
                if (CancellationToken.IsCancellationRequested)
                {
                    throw;
                }
                else
                {
                    // スキップ時は即時移動
                    await ViewController.ScrollPage(
                        1.0f,
                        0,
                        CancellationToken);
                }
            }
            finally
            {
                ViewController.SetElasticScroll();
                ViewController.ShowBattleFieldBlackCurtain();
            }
        }

        async UniTask PlayDefenseTargetHighlight(
            DefenseTargetModel defenseTargetModel,
            bool isScrollablePage,
            CancellationToken cancellationToken)
        {
            _battleStartDefenseTargetHighlightCancellationTokenSource = new CancellationTokenSource();
            var linkedToken = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken,
                _battleStartDefenseTargetHighlightCancellationTokenSource.Token).Token;

            try
            {
                // 半透明黒表示と防衛オブジェクトを前面に
                ViewController.SetDefenseTargetHighlight(true);

                // スクロール必要なぐらい縦長であれば防衛オブジェクトに画面を合わせる
                if (isScrollablePage)
                {
                    var scrollPos =
                        ViewCoordinateConverter.ToFieldViewCoord(defenseTargetModel.BattleSide, defenseTargetModel.Pos);
                    await ViewController.ScrollPage(
                        scrollPos,
                        1.0f,
                        linkedToken,
                        isIndependentUpdate: true);
                }

                await UniTask.Delay(2000, cancellationToken: linkedToken);

                // 画面一番上に戻す
                await ViewController.ScrollPage(
                    1.0f,
                    0.2f,
                    linkedToken,
                    isIndependentUpdate: true);
                _battleStartDefenseTargetHighlightCancellationTokenSource.Dispose();
                _battleStartDefenseTargetHighlightCancellationTokenSource = null;
            }
            catch (OperationCanceledException)
            {
                if (CancellationToken.IsCancellationRequested)
                {
                    throw;
                }
                else
                {
                    // スキップ時は即時移動
                    await ViewController.ScrollPage(
                        1.0f,
                        0,
                        CancellationToken);
                }
            }
            finally
            {
                ViewController.SetDefenseTargetHighlight(false);
            }
        }

        async UniTask PlayPlayerOutpostHpHighlight()
        {
            _battleStartPlayerOutpostHpHighlightCancellationTokenSource = new CancellationTokenSource();
            var linkedToken = CancellationTokenSource.CreateLinkedTokenSource(CancellationToken,
                _battleStartPlayerOutpostHpHighlightCancellationTokenSource.Token).Token;

            try
            {
                // 半透明黒表示と味方ゲートHPを前面に
                ViewController.SetPlayerOutpostHpHighlight(true);

                await UniTask.Delay(2000, cancellationToken: linkedToken);

                _battleStartPlayerOutpostHpHighlightCancellationTokenSource.Dispose();
                _battleStartPlayerOutpostHpHighlightCancellationTokenSource = null;
            }
            catch (OperationCanceledException)
            {
                if (CancellationToken.IsCancellationRequested)
                {
                    throw;
                }
                else
                {
                    // スキップ時は即時移動
                    await ViewController.ScrollPage(
                        1.0f,
                        0,
                        CancellationToken);
                }
            }
            finally
            {
                ViewController.SetPlayerOutpostHpHighlight(false);
            }
        }

        async UniTask PlayBattleStartNoiseAnimation(CancellationToken cancellationToken)
        {
            _battleStartNoiseAnimationCancellationTokenSource = new CancellationTokenSource();

            var linkedCancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken,
                _battleStartNoiseAnimationCancellationTokenSource.Token);

            try
            {
                await ViewController
                    .PlayBattleStartNoiseAnimation(linkedCancellationTokenSource.Token)
                    .SuppressCancellationThrow();
            }
            catch (OperationCanceledException)
            {
                SoundEffectPlayer.Stop();
                cancellationToken.ThrowIfCancellationRequested();
            }
            finally
            {
                _battleStartNoiseAnimationCancellationTokenSource?.Dispose();
                _battleStartNoiseAnimationCancellationTokenSource = null;

                linkedCancellationTokenSource.Dispose();
            }
        }

        bool CancelBattleStartScroll()
        {
            if (_battleStartScrollCancellationTokenSource == null) return false;

            _battleStartScrollCancellationTokenSource.Cancel();
            _battleStartScrollCancellationTokenSource.Dispose();
            _battleStartScrollCancellationTokenSource = null;

            return true;
        }

        bool CancelBattleStartMangaAnimation()
        {
            if (_battleStartMangaAnimationCancellationTokenSource == null) return false;

            _battleStartMangaAnimationCancellationTokenSource.Cancel();
            _battleStartMangaAnimationCancellationTokenSource.Dispose();
            _battleStartMangaAnimationCancellationTokenSource = null;

            return true;
        }

        bool CancelBattleEndMangaAnimation()
        {
            if (_gameEndMangaAnimationCancellationTokenSource == null) return false;

            _gameEndMangaAnimationCancellationTokenSource.Cancel();
            _gameEndMangaAnimationCancellationTokenSource.Dispose();
            _gameEndMangaAnimationCancellationTokenSource = null;

            return true;
        }

        bool CancelDefenseTargetHighlight()
        {
            if (_battleStartDefenseTargetHighlightCancellationTokenSource == null) return false;

            _battleStartDefenseTargetHighlightCancellationTokenSource.Cancel();
            _battleStartDefenseTargetHighlightCancellationTokenSource.Dispose();
            _battleStartDefenseTargetHighlightCancellationTokenSource = null;

            return true;
        }

        bool CancelPlayerOutpostHpHighlight()
        {
            if (_battleStartPlayerOutpostHpHighlightCancellationTokenSource == null) return false;

            _battleStartPlayerOutpostHpHighlightCancellationTokenSource.Cancel();
            _battleStartPlayerOutpostHpHighlightCancellationTokenSource.Dispose();
            _battleStartPlayerOutpostHpHighlightCancellationTokenSource = null;

            return true;
        }

        bool CancelBattleStartNoiseAnimation()
        {
            if (_battleStartNoiseAnimationCancellationTokenSource == null) return false;

            _battleStartNoiseAnimationCancellationTokenSource.Cancel();
            _battleStartNoiseAnimationCancellationTokenSource.Dispose();
            _battleStartNoiseAnimationCancellationTokenSource = null;

            return true;
        }

        async UniTask ShowStartAnimationView(CancellationToken cancellationToken)
        {
            bool isClosed = false;
            var argument = new InGameStartAnimationViewController.Argument(() => isClosed = true);

            var viewController =
                ViewFactory.Create<InGameStartAnimationViewController, InGameStartAnimationViewController.Argument>(argument);

            ViewController.Show(viewController, animated: false);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask ShowVictoryAnimationView(CancellationToken cancellationToken)
        {
            bool isClosed = false;
            var argument = new VictoryAnimationViewController.Argument(() => isClosed = true);

            var victoryFxViewController =
                ViewFactory.Create<VictoryAnimationViewController, VictoryAnimationViewController.Argument>(argument);

            ViewController.Show(victoryFxViewController, animated: false);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask<(bool isRetry, StaminaBoostCount staminaBoostCount)> ShowVictoryResultViewAndGetRetryInfo(
            VictoryResultModel victoryResultModel,
            CancellationToken cancellationToken)
        {
            var userExpGainViewModels = victoryResultModel.UserExpGains
                .Select((model, index) =>
                {
                    bool isLevelUp = index > 0;
                    return new UserExpGainViewModel(
                        model.Level,
                        model.StartExp,
                        model.EndExp,
                        model.NextLevelExp,
                        isLevelUp);
                })
                .ToList();

            var acquiredPlayerResourcesGroupedByStaminaRap = victoryResultModel.AcquiredPlayerResourcesGroupedByStaminaRap
                .Select(group => PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(group, true))
                .ToList();

            var viewModel = new VictoryResultViewModel(
                CharacterStandImageAssetPath.FromAssetKey(victoryResultModel.PickupUnitAssetKey),
                userExpGainViewModels,
                UserLevelUpViewModelTranslator.ToUserLevelUpResultViewModel(victoryResultModel.UserLevelUpEffect),
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(
                    victoryResultModel.AcquiredPlayerResources,
                    true),
                acquiredPlayerResourcesGroupedByStaminaRap,
                ArtworkFragmentAcquisitionViewModelTranslator.ToTranslate(victoryResultModel.ArtworkFragmentAcquisitionModels),
                ResultSpeedAttackViewModelTranslator.Translate(victoryResultModel.ResultSpeedAttackModel),
                victoryResultModel.RemainingEventCampaignTimeSpan,
                victoryResultModel.InGameRetryModel.IsRetryAvailable);

            bool isClosed = false;
            bool isRetry = false;
            var staminaBoostCount = StaminaBoostCount.One;
            var argument = new VictoryResultViewController.Argument(
                viewModel,
                () => isClosed = true,
                async () =>
                {
                    (isRetry, staminaBoostCount) = await RetryButtonAction(cancellationToken);
                });

            var victoryResultViewController =
                ViewFactory.Create<VictoryResultViewController, VictoryResultViewController.Argument>(argument);

            ViewController.Show(victoryResultViewController, animated: false);

            // 所持上限時のメールボックス付与モーダル表示
            await ShowUnreceivedRewardReasonsIfNeeded(victoryResultModel.UnreceivedRewardReasonTypes);

            // リトライまたはクローズが押されるまで待機
            await UniTask.WaitUntil(() => isClosed || isRetry, cancellationToken: cancellationToken);

            return (isRetry, staminaBoostCount);
        }

        async UniTask ShowUnreceivedRewardReasonsIfNeeded(IReadOnlyList<UnreceivedRewardReasonType> unreceivedRewardReasonTypes)
        {
            if (unreceivedRewardReasonTypes.Count == 0) return;
            if (unreceivedRewardReasonTypes
                .All(u => u == UnreceivedRewardReasonType.None)) return;

            if (unreceivedRewardReasonTypes
                .Exists(t => t == UnreceivedRewardReasonType.SentToMessage))
            {
                var completionTokenSource = new UniTaskCompletionSource();
                UnreceivedRewardWireframe.ShowSentToMailbox(() => completionTokenSource.TrySetResult());
                await completionTokenSource.Task;
            }

            if (unreceivedRewardReasonTypes
                .Exists(t => t == UnreceivedRewardReasonType.ResourceLimitReached))
            {
                var completionTokenSource = new UniTaskCompletionSource();
                UnreceivedRewardWireframe.ShowResourceLimitReached(() => completionTokenSource.TrySetResult());
                await completionTokenSource.Task;
            }

            if (unreceivedRewardReasonTypes
                .Exists(t => t == UnreceivedRewardReasonType.ResourceOverflowDiscarded))
            {
                var completionTokenSource = new UniTaskCompletionSource();
                UnreceivedRewardWireframe.ShowResourceOverflowDiscarded(() => completionTokenSource.TrySetResult());
                await completionTokenSource.Task;
            }

        }

        async UniTask<(bool isRetry, StaminaBoostCount staminaBoostCount)> ShowDefeatResultViewAndGetRetryInfo(
            DefeatResultModel resultModel,
            CancellationToken cancellationToken)
        {
            var viewModel = DefeatResultViewModelTranslator.ToDefeatResultViewModel(resultModel);

            var isClosed = false;
            var isRetry = false;
            var staminaBoostCount = StaminaBoostCount.One;
            var argument = new DefeatResultViewController.Argument(
                viewModel,
                () => isClosed = true,
                async () =>
                {
                    (isRetry, staminaBoostCount) = await RetryButtonAction(cancellationToken);
                });

            var defeatResultViewController =
                ViewFactory.Create<DefeatResultViewController, DefeatResultViewController.Argument>(argument);

            ViewController.Show(defeatResultViewController, animated: false);

            // リトライまたはクローズが押されるまで待機
            await UniTask.WaitUntil(() => isClosed || isRetry, cancellationToken: cancellationToken);

            return (isRetry, staminaBoostCount);
        }

        async UniTask ShowFinishAnimationView(PvpResultModel pvpResultModel, CancellationToken cancellationToken)
        {
            if (pvpResultModel.IsEmpty())
            {
                await ShowDefaultFinishAnimationView(cancellationToken);
            }
            else
            {
                await ShowPvpFinishAnimationView(pvpResultModel, cancellationToken);
            }
        }

        async UniTask ShowDefaultFinishAnimationView(CancellationToken cancellationToken)
        {
            bool isClosed = false;
            var argument = new FinishAnimationViewController.Argument(() => isClosed = true);

            var finishFxViewController =
                ViewFactory.Create<FinishAnimationViewController, FinishAnimationViewController.Argument>(argument);

            ViewController.Show(finishFxViewController, animated: false);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask ShowPvpFinishAnimationView(PvpResultModel pvpResultModel, CancellationToken cancellationToken)
        {
            bool isClosed = false;

            var viewModel = PvpResultViewModelTranslator.ToViewModel(pvpResultModel);
            var argument = new PvpBattleFinishAnimationViewController.Argument(viewModel);

            var pvpFinishAnimationViewController = ViewFactory.Create<
                PvpBattleFinishAnimationViewController,
                PvpBattleFinishAnimationViewController.Argument>(argument);
            pvpFinishAnimationViewController.OnCloseButtonTappedAction = () => isClosed = true;
            ViewController.PresentModally(pvpFinishAnimationViewController, animated: false);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask<(bool isRetry, StaminaBoostCount staminaBoostCount)> ShowFinishResultViewAndGetRetryInfo(
            VictoryResultModel victoryResultModel,
            CancellationToken cancellationToken)
        {
            var userExpGainViewModels = victoryResultModel.UserExpGains
                .Select((model, index) =>
                {
                    bool isLevelUp = index > 0;
                    return new UserExpGainViewModel(
                        model.Level,
                        model.StartExp,
                        model.EndExp,
                        model.NextLevelExp,
                        isLevelUp);
                })
                .ToList();

            var viewModel = new FinishResultViewModel(
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(
                    victoryResultModel.AcquiredPlayerResources,
                    true),
                victoryResultModel.ResultScoreModel,
                victoryResultModel.RemainingEventCampaignTimeSpan,
                victoryResultModel.InGameRetryModel.IsRetryAvailable
                );

            var isClosed = false;
            var isRetry = false;
            var staminaBoostCount = StaminaBoostCount.One;
            var argument = new FinishResultViewController.Argument(
                viewModel,
                () => isClosed = true,
                async () =>
                {
                    (isRetry, staminaBoostCount) = await RetryButtonAction(cancellationToken);
                });

            var finishResultViewController =
                ViewFactory.Create<FinishResultViewController, FinishResultViewController.Argument>(argument);

            ViewController.Show(finishResultViewController, animated: false);

            // 所持上限時のメールボックス付与モーダル表示
            await ShowUnreceivedRewardReasonsIfNeeded(victoryResultModel.UnreceivedRewardReasonTypes);

            // リトライまたはクローズが押されるまで待機
            await UniTask.WaitUntil(() => isClosed || isRetry, cancellationToken: cancellationToken);

            return (isRetry, staminaBoostCount);
        }

        async UniTask<(bool isRetry, StaminaBoostCount staminaBoostCount)> ShowAdventBattleResultViewAndGetRetryInfo(
            VictoryResultModel victoryResultModel,
            CancellationToken cancellationToken)
        {
            var viewModel = new AdventBattleResultViewModel(
                PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(
                    victoryResultModel.AcquiredPlayerResources,
                    true),
                victoryResultModel.ResultScoreModel.CurrentScore.ToAdventBattleScore(),
                victoryResultModel.ResultScoreModel.HighScore.ToAdventBattleScore(),
                victoryResultModel.ResultScoreModel.NewRecordFlag,
                AdventBattleResultScoreViewModelFactory.CreateAdventBattleResultScoreViewModel(
                    victoryResultModel.AdventBattleResultScoreModel),
                victoryResultModel.RemainingEventCampaignTimeSpan,
                UserLevelUpViewModelTranslator.ToUserLevelUpResultViewModel(victoryResultModel.UserLevelUpEffect),
                victoryResultModel.InGameRetryModel.IsRetryAvailable);

            var isClosed = false;
            var isRetry = false;
            var staminaBoostCount = StaminaBoostCount.One;
            var argument = new AdventBattleResultViewController.Argument(viewModel);

            var adventBattleResultViewController =
                ViewFactory.Create<AdventBattleResultViewController, AdventBattleResultViewController.Argument>(argument);
            adventBattleResultViewController.OnCloseAction = () => isClosed = true;
            adventBattleResultViewController.OnRetryAction = async () =>
            {
                (isRetry, staminaBoostCount) = await RetryButtonAction(cancellationToken);
            };

            ViewController.Show(adventBattleResultViewController, animated: true);

            // 所持上限時のメールボックス付与モーダル表示
            await ShowUnreceivedRewardReasonsIfNeeded(victoryResultModel.UnreceivedRewardReasonTypes);

            // リトライまたはクローズが押されるまで待機
            await UniTask.WaitUntil(() => isClosed || isRetry, cancellationToken: cancellationToken);

            return (isRetry, staminaBoostCount);
        }

        async UniTask ShowPvpResultView(
            VictoryResultModel victoryResultModel,
            CancellationToken cancellationToken)
        {
            var viewModel = PvpBattleResultPointViewModelFactory.CreatePvpResultPointViewModel(
                victoryResultModel.PvpBattleResultPointModel);

            bool isClosed = false;
            var argument = new PvpBattleResultViewController.Argument(viewModel);
            var controller = ViewFactory.Create<PvpBattleResultViewController, PvpBattleResultViewController.Argument>(argument);
            controller.OnCloseAction = () => isClosed = true;
            ViewController.Show(controller, animated: true);

            // 所持上限時のメールボックス付与モーダル表示
            await ShowUnreceivedRewardReasonsIfNeeded(victoryResultModel.UnreceivedRewardReasonTypes);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask ShowDefeatDialogView(
            StageEndConditionType battleEndConditionType,
            CancellationToken cancellationToken)
        {
            // 現状防衛オブジェクトのみのため簡素に
            string description = battleEndConditionType switch
            {
                StageEndConditionType.DefenseTargetBreakDown => "ターゲットの防衛失敗",
                _ => String.Empty,
            };

            var viewModel = new DefeatDialogViewModel(new DefeatDescription(description));
            var argument = new DefeatDialogViewController.Argument(viewModel);

            bool isClosed = false;
            var defeatDialogViewController =
                ViewFactory.Create<DefeatDialogViewController, DefeatDialogViewController.Argument>(argument);
            defeatDialogViewController.OnCloseAction = () => isClosed = true;
            ViewController.Show(defeatDialogViewController, animated: true);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        /// <summary> スペシャルユニットのスキル発動前の確認ダイアログ表示 </summary>
        void ShowSpecialUnitUseSkillConfirmationDialog(
            MasterDataId characterId,
            NeedTargetSelectTypeFlag needTargetSelectTypeFlag)
        {
            var dialogUseCaseModel = SpecialUnitSummonConfirmationDialogUseCase.GetUseCaseModel(
                characterId,
                needTargetSelectTypeFlag);

            var viewModel = SpecialUnitSummonConfirmationDialogViewModelTranslator.Translate(dialogUseCaseModel);
            var argument = new SpecialUnitSummonConfirmationDialogViewController.Argument(viewModel);

            _specialUnitSummonConfirmationDialogViewController = ViewFactory.Create<
                SpecialUnitSummonConfirmationDialogViewController,
                SpecialUnitSummonConfirmationDialogViewController.Argument>(argument);
            _specialUnitSummonConfirmationDialogViewController.OnUseSkill = () => SummonSpecialUnitToCenterOfDisplay(characterId);
            _specialUnitSummonConfirmationDialogViewController.OnCancel = EndSpecialUnitSummon;

            ViewController.Show(_specialUnitSummonConfirmationDialogViewController, animated: true);
        }

        void CloseSpecialUnitUseSkillConfirmationDialog()
        {
            _specialUnitSummonConfirmationDialogViewController?.Close();
            _specialUnitSummonConfirmationDialogViewController = null;
        }

        /// <summary> 必殺技の範囲指定がコマ選択を必要としないものだった場合に現在表示中のページの真ん中に該当するコマに召喚する </summary>
        bool SummonSpecialUnitToCenterOfDisplay(MasterDataId characterId)
        {
            var displayingPageCenter = ViewController.GetDisplayingPageCenter();
            return SelectSpecialUnitSummonTarget(characterId, displayingPageCenter);
        }

        void EndSpecialUnitSummon()
        {
            EndSpecialUnitSummonPositionSelection();
            EndSpecialUnitSkillConfirmationDialog();
        }

        void EndSpecialUnitSummonPositionSelection()
        {
            CancelSpecialUnitSummonUseCase.CancelSpecialUnitSummon();

            ViewController.EndKomaSelection();
            ViewController.ResetTouchCallBack();

            _isSpecialUnitSummonSelecting = false;
            _specialUnitSummonKomaRange = SpecialUnitSummonKomaRange.Empty;
        }

        void EndSpecialUnitSkillConfirmationDialog()
        {
            CloseSpecialUnitUseSkillConfirmationDialog();
            ViewController.EndSpecialUnitSkillConfirmationWindow();
        }

        void CancelSummonSpecialUnit()
        {
            foreach (var tokenSource in _specialUnitSummonCancellationTokenSources)
            {
                tokenSource.Cancel();
            }

            _specialUnitSummonCancellationTokenSources.Clear();
        }

        async UniTask<ContinueConfirmationResult> ConfirmContinue(CancellationToken cancellationToken)
        {
            var continueActionSelectionModel = GetContinueActionSelectionUseCase.GetModel();
            ContinueConfirmationResult continueConfirmationResult = await ShowContinueView(
                continueActionSelectionModel,
                cancellationToken);

            return continueConfirmationResult;
        }

        async UniTask<ContinueConfirmationResult> ShowContinueView(
            ContinueActionSelectionModel continueActionSelectionModel,
            CancellationToken cancellationToken)
        {
            var continueViewModel = ContinueActionSelectionViewModelTranslator.ToViewModel(continueActionSelectionModel);

            bool isClosed = false;
            var continueViewResult = ContinueActionSelectionViewController.Result.Cancel;

            var argument = new ContinueActionSelectionViewController.Argument(
                continueViewModel,
                result =>
                {
                    continueViewResult = result;
                    isClosed = true;
                });

            var viewController = ViewFactory
                .Create<ContinueActionSelectionViewController, ContinueActionSelectionViewController.Argument>(argument);

            ViewController.PresentModally(viewController);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);

            return continueViewResult switch
            {
                ContinueActionSelectionViewController.Result.Cancel => ContinueConfirmationResult.Cancel,
                ContinueActionSelectionViewController.Result.QuestPeriodOutside => ContinueConfirmationResult.QuestPeriodOutside,
                ContinueActionSelectionViewController.Result.Continue => ContinueConfirmationResult.Continue,
                _ => ContinueConfirmationResult.Cancel
            };
        }

        async UniTask ShowDiamondPurchaseView(CancellationToken cancellationToken)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            var isClosed = false;
            var argument = new DiamondPurchaseViewController.Argument(() => isClosed = true);

            var viewController =
                ViewFactory.Create<DiamondPurchaseViewController, DiamondPurchaseViewController.Argument>(argument);

            ViewController.PresentModally(viewController);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        async UniTask PreLoadIntroductionManga(CancellationToken cancellationToken)
        {
            await IntroductionTutorialContext.DoIfPreLoadIntroductionTutorial(async () =>
                {
                    await ViewController.LoadTutorialIntroductionManga(cancellationToken);
                }
            );
        }

        async UniTask PlayIntroductionTutorial()
        {
            _isPlayingTutorial = true;

            // チュートリアル実行
            await IntroductionTutorialContext.DoIfTutorial(() => UniTask.CompletedTask);

            _isPlayingTutorial = false;
        }

        async UniTask PlayTutorial()
        {
            _isPlayingTutorial = true;

            // チュートリアル実行
            await TutorialContext.DoIfTutorial(() => UniTask.CompletedTask);

            _isPlayingTutorial = false;
        }

        void ITutorialInGameViewDelegate.TutorialPauseInGame()
        {
            _tutorialPauseHandler?.Dispose();
            _tutorialPauseHandler = _battlePauseController.TurnOn();

            ViewController.EnableTapGuard();

            _viewPauseHandlerForTutorial?.Dispose();
            _viewPauseHandlerForTutorial = ViewController.PauseBattleField();
        }

        void ITutorialInGameViewDelegate.TutorialResumeInGame()
        {
            ViewController.DisableTapGuard();

            _viewPauseHandlerForTutorial.Dispose();
            _viewPauseHandlerForTutorial = null;

            _tutorialPauseHandler.Dispose();
            _tutorialPauseHandler = null;
        }

        void ITutorialInGameViewDelegate.TutorialTransitionToHome()
        {
            TransitionToHome();
        }

        void ITutorialInGameViewDelegate.SetFullRushGauge()
        {
            TutorialChargeRushGaugeUseCase.ChargeRushGauge();
        }

        void ITutorialInGameViewDelegate.SetSummonCostToZero()
        {
            TutorialChangeSummonCostToZeroUseCase.ChangeSummonCostToZero();
        }

        void ITutorialInGameViewDelegate.SetOneUnitSummonCostToZero()
        {
            TutorialChangeFirstUnitSummonCostToZero.ChangeFirstUnitSummonCostToZero();
        }

        void ITutorialInGameViewDelegate.SetFirstUnitSpecialAttackCoolTimeToZero()
        {
            TutorialChangeFirstUnitRemainingSpecialAttackCoolTimeToZeroUseCase
                .ChangeFirstUnitRemainingSpecialAttackCoolTimeToZero();
        }

        void ITutorialInGameViewDelegate.SkipTutorial()
        {
            IntroductionTutorialSkipUseCase.SkipIntroductionTutorial();
            _battlePauseController.TurnOn();
        }

        async UniTask ITutorialInGameViewDelegate.AwaitStartInGame(CancellationToken cancellationToken)
        {
            // インゲーム開始までチュートリアルを待機させる
            await UniTask.WaitUntil(() => _isStarted, cancellationToken: cancellationToken);
        }

        void ITutorialInGameViewDelegate.SetPlayingTutorialFlag(bool isPlayingTutorial)
        {
            _isPlayingTutorial = isPlayingTutorial;
        }

        void ITutorialInGameViewDelegate.SetPlayingUnitDetailTutorialFlag(bool isPlayingUnitDetailTutorial)
        {
            _isPlayingUnitDetailTutorial = isPlayingUnitDetailTutorial;
        }

        TutorialIntroductionMangaManager ITutorialInGameViewDelegate.GetTutorialIntroductionMangaManager()
        {
            return ViewController.GetTutorialIntroductionMangaManager();
        }

        void ITutorialInGameViewDelegate.DisableRushAnimSkip()
        {
            ViewController.DisableRushAnimSkip();
        }

        bool CheckSpecialAttackCutInSkipAndUpdateIdList(MasterDataId characterId)
        {
            switch (_specialAttackCutInPlayType)
            {
                case SpecialAttackCutInPlayType.OnceADay:
                    // 1日1回 : 既に再生済みのキャラはスキップ、再生されていないキャラはIDを記録
                    if (!_playedSpecialAttackCutInUnitIds.Contains(characterId))
                    {
                        _playedSpecialAttackCutInUnitIds.Add(characterId);
                        return false;
                    }

                    // OnceADay用のリストにIDを記録済みの場合はスキップ(その1日の中で記録済み)
                    return true;

                case SpecialAttackCutInPlayType.On:
                    // 1日1回再生済みIDにも追加
                    if (!_playedSpecialAttackCutInUnitIds.Contains(characterId))
                    {
                        _playedSpecialAttackCutInUnitIds.Add(characterId);
                    }

                    return false;

                case SpecialAttackCutInPlayType.Off:
                    // OFF : 記録せずスキップする
                    return true;
            }

            // スキップしない
            return false;
        }

        void ChangeDeckMode()
        {
            ViewController.ChangeDeckMode();
            ChangeDeckUseCase.ChangeDeckLayout();
        }

        void SetDamageDisplay(DamageDisplayFlag isDamageDisplay)
        {
            ViewController.SetDamageDisplay(isDamageDisplay);
        }

        void SetupTimeCountDown()
        {
            _timeCountDowns = new List<TimeCountDown>
            {
                new(TimeCountDown.EnumTimeCountDownType.LeftTime30, false),
                new(TimeCountDown.EnumTimeCountDownType.LeftTime20, false),
                new(TimeCountDown.EnumTimeCountDownType.LeftTime10, false),
            };
        }

        void ResetTimeScale()
        {
            _battleSpeedTimeScaleControlHandler?.Dispose();
            _battleSpeedTimeScaleControlHandler = null;
        }

        void SetUpKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            ViewController.SetUpKomas(komaDictionary);
        }

#if GLOW_INGAME_DEBUG
        void DebugCommandActivated(IDebugCommandPresenter debugCommandPresenter)
        {
            ApplicationLog.Log(nameof(InGamePresenter), nameof(DebugCommandActivated));

            debugCommandPresenter.CreateRootMenu = DebugCommandHandler.CreateDebugCommandRootMenu;

            // 進行を一時停止
            _debugCommandPauseHandler?.Dispose();
            _debugCommandPauseHandler = _battlePauseController.TurnOn();

            _viewPauseHandlerForDebugCommand?.Dispose();
            _viewPauseHandlerForDebugCommand = ViewController.PauseBattleField();
        }

        void DebugCommandInactivated()
        {
            DisposeDebugCommandPauseHandler();
        }

        void DisposeDebugCommandPauseHandler()
        {
            _debugCommandPauseHandler?.Dispose();
            _debugCommandPauseHandler = null;

            _viewPauseHandlerForDebugCommand?.Dispose();
            _viewPauseHandlerForDebugCommand = null;
        }

        void PlayDebugCutIn(CharacterUnitModel unitModel, UnitAttackViewInfo attackViewInfo)
        {
            var animation = new SpecialAttackCutInAnimation(
                ViewController,
                unitModel.Id,
                unitModel.Color,
                unitModel.AssetKey,
                attackViewInfo,
                SpecialAttackCutInSelfPauseFlag.False);

            _inGameInterruptAnimationDirector.Enqueue(animation);
        }

        void DebugPause(bool isPaused)
        {
            if (isPaused)
            {
                _debugPauseHandler?.Dispose();
                _debugPauseHandler = _battlePauseController.TurnOn();

                _debugViewPauseHandler?.Dispose();
                _debugViewPauseHandler = ViewController.PauseBattleField();
            }
            else
            {
                _debugPauseHandler?.Dispose();
                _debugViewPauseHandler?.Dispose();
            }
        }
#endif
    }
}
