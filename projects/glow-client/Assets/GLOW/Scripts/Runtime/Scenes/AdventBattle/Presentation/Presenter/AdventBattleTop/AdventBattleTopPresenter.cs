using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using GLOW.Scenes.AdventBattle.Presentation.Calculator;
using GLOW.Scenes.AdventBattle.Presentation.Translator;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.AdventBattle.Presentation.View.AdventBattleInfo;
using GLOW.Scenes.AdventBattleRanking.Domain.UseCases;
using GLOW.Scenes.AdventBattleRanking.Presentation.Translators;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;
using GLOW.Scenes.AdventBattleRewardList.Presentation.View;
using GLOW.Scenes.EnhanceQuestTop.Domain.UseCases;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageLimitStatusView;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Presentation.Presenter
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-1_降臨バトルトップ
    /// </summary>
    public class AdventBattleTopPresenter : IAdventBattleTopViewDelegate
    {
        [Inject] AdventBattleTopViewController ViewController { get; }
        [Inject] FetchAdventBattleTopInfoUseCase FetchAdventBattleTopInfoUseCase { get; }
        [Inject] SetPartyFormationEventBonusUseCase SetPartyFormationEventBonusUseCase { get; }
        [Inject] ReceiveAdventBattleScoreRewardsUseCase ReceiveAdventBattleScoreRewardsUseCase { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IAdventBattleHighScoreGaugeRateCalculator AdventBattleHighScoreGaugeRateCalculator { get; }
        [Inject] AdventBattleRankingUseCase AdventBattleRankingUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] AdventBattleStartUseCase AdventBattleStartUseCase { get; }
        [Inject] AdventBattleWireFrame AdventBattleWireFrame { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] ShowRaidAdventBattleTutorialDialogUseCase ShowRaidAdventBattleTutorialDialogUseCase { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] CheckFreePartTutorialCompletedUseCase CheckFreePartTutorialCompletedUseCase { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }


        CancellationToken AdventBattleTopCancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        // イベントボーナスグループID
        EventBonusGroupId _eventBonusGroupId;
        MasterDataId _mstAdventBattleId;
        AdventBattleRankingCalculatingFlag _calculatingRankings;
        AdventBattleTopUseCaseModel _adventBattleTopModel;
        bool _isShowingTutorialDialog;

        AdventBattleChallengeType _challengeType;
        
        AdventBattleTopViewModel _viewModel;

        void IAdventBattleTopViewDelegate.OnViewDidLoad()
        {
            // 降臨バトルTOP表示に必要な情報を取得
            _adventBattleTopModel = FetchAdventBattleTopInfoUseCase.FetchAdventBattleTop();
            var gaugeModel = AdventBattleHighScoreGaugeRateCalculator.CalculateHighScoreGaugeRate(
                _adventBattleTopModel.HighScoreRewards,
                _adventBattleTopModel.MaxScore,
                _adventBattleTopModel.HighScoreLastAnimationPlayed);
            var calculatingRankings = _adventBattleTopModel.CalculatingRankings(TimeProvider.Now);
            
            _viewModel = AdventBattleTopViewModelTranslator.ToViewModel(
                _adventBattleTopModel, 
                gaugeModel,
                calculatingRankings);
            _eventBonusGroupId = _viewModel.EventBonusGroupId;
            _mstAdventBattleId = _viewModel.MstAdventBattleId;
            _calculatingRankings = _viewModel.CalculatingRankings;
            _challengeType = _viewModel.AdventBattleChallengeType;

            LoadAdventBattleTopBackground(_viewModel.KomaBackgroundAssetPath);

            DoAsync.Invoke(AdventBattleTopCancellationToken, async cancellationToken =>
            {
                ReceivedAdventBattleScoreRewardsModel receivedRewards;
                using (ScreenInteractionControl.Lock())
                {
                    LoadAndSetupEnemyUnits(
                        cancellationToken,
                        _viewModel.DisplayEnemyUnitFirst,
                        _viewModel.DisplayEnemyUnitSecond,
                        _viewModel.DisplayEnemyUnitThird).Forget();

                    ViewController.SetupAdventBattleTopView(_viewModel);
                    ViewController.SetAdventBattleMissionBadge(_viewModel.MissionBadge);

                    receivedRewards = await ReceiveRewardsForAdventBattleScore(
                        cancellationToken,
                        _viewModel.MstAdventBattleId);
                }

                // 初遷移時チュートリアルダイアログ表示
                await ShouldShowAdventBattleTutorialDialog(cancellationToken);

                using (ViewController.ViewTapGuard())
                {
                    // アニメーション開始
                    await PlayHighScoreGaugeScrollAnimation(cancellationToken, _viewModel.HighScoreGaugeViewModel);

                    // ゲージだけが伸びる場合もあるので、報酬がない場合は報酬受け取り演出を行わない
                    if (receivedRewards.IsEmpty()) return;

                    var maxScoreRewardIconViewModels =
                        receivedRewards.MaxScoreRewards
                            .Select(r =>
                                CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                            .ToList();
                    var raidTotalScoreRewardIconViewModels =
                        receivedRewards.RaidTotalScoreRewards
                            .Select(r =>
                                CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                            .ToList();

                    if (!maxScoreRewardIconViewModels.IsEmpty())
                    {
                        await CommonReceiveWireFrame.ShowAsync(
                            cancellationToken,
                            maxScoreRewardIconViewModels,
                            new RewardTitle("ハイスコア報酬"),
                            ReceivedRewardDescription.Empty);
                    }

                    if (!raidTotalScoreRewardIconViewModels.IsEmpty())
                    {
                        CommonReceiveWireFrame.Show(
                            raidTotalScoreRewardIconViewModels,
                            new RewardTitle("協力スコア報酬"),
                            GetAdventBattleReceivedRaidRewardDescription(receivedRewards.AdventBattleRaidTotalScore),
                            () => { });
                    }

                    HomeHeaderDelegate.UpdateStatus();

                    // 入手状況を演出終了後のものに更新
                    var updatedRewardList = _adventBattleTopModel.HighScoreRewardsUpdated
                        .Select(AdventBattleHighScoreRewardViewModelTranslator.ToViewModel)
                        .ToList();
                    ViewController.UpdateHighScoreRewardsAfterObtained(updatedRewardList);
                }
            });

            // ランキングの集計状況を監視
            if (_calculatingRankings)
            {
                MonitorAdventBattleRankingStatus();
            }
        }

        void IAdventBattleTopViewDelegate.OnViewWillAppear()
        {
            ViewController.PlayPickUpRewardEffect();
            ViewController.UpdatePartyName(GetCurrentPartyNameUseCase.GetCurrentPartyName());
            ViewController.SetUpAdventBattleScoreComponent(_viewModel);
        }

        void IAdventBattleTopViewDelegate.OnHelpButtonTapped()
        {
            // 降臨バトルダイアログ表示後に協力バトルダイアログを表示する
            Action dialogClosedAction = () =>
            {
                var functionName = HelpDialogIdDefinitions.RaidAdventBattle;
                TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, functionName);
            };

            var functionName = HelpDialogIdDefinitions.AdventBattle;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, functionName, dialogClosedAction);
        }

        void IAdventBattleTopViewDelegate.OnEnemyDetailButtonTapped()
        {
            var argument = new AdventBattleInfoViewController.Argument(_mstAdventBattleId);
            AdventBattleWireFrame.ShowEnemyDetail(ViewController, argument);
        }

        void IAdventBattleTopViewDelegate.OnRankingButtonTapped()
        {
            DoAsync.Invoke(AdventBattleTopCancellationToken, ScreenInteractionControl, async cancellationToken =>
            {
                if (_calculatingRankings)
                {
                    CommonToastWireFrame.ShowScreenCenterToast("現在ランキング結果の集計中になります\n集計終了までお待ちください");
                    return;
                }

                var rankingModel = await AdventBattleRankingUseCase.GetAdventBattleRanking(cancellationToken, _mstAdventBattleId);
                var viewModel = AdventBattleRankingViewModelTranslator.ToViewModel(rankingModel);
                var argument = new AdventBattleRankingViewController.Argument(viewModel);
                AdventBattleWireFrame.ShowRankingView(argument);
            });
        }

        void IAdventBattleTopViewDelegate.OnRewardListButtonTapped()
        {
            var argument = new AdventBattleRewardListViewController.Argument(_mstAdventBattleId);
            AdventBattleWireFrame.ShowAdventBattleRewardListView(argument);
        }

        void IAdventBattleTopViewDelegate.OnMissionButtonTapped()
        {
            AdventBattleWireFrame.ShowAdventBattleMissionView(ViewController, ViewController.SetAdventBattleMissionBadge);
        }

        void IAdventBattleTopViewDelegate.OnBonusUnitButtonTapped()
        {
            var argument = new EventBonusUnitListViewController.Argument(_eventBonusGroupId, MasterDataId.Empty);
            AdventBattleWireFrame.ShowBonusUnitView(ViewController, argument);
        }

        void IAdventBattleTopViewDelegate.OnPartyFormationButtonTapped()
        {
            // 副作用
            SetPartyFormationEventBonusUseCase.SetEventBonus(_eventBonusGroupId);

            var argument = new HomePartyFormationViewController.Argument(
                _mstAdventBattleId,
                InGameContentType.AdventBattle,
                _eventBonusGroupId,
                MasterDataId.Empty);
            AdventBattleWireFrame.ShowPartyFormationView(argument);
        }

        void IAdventBattleTopViewDelegate.OnBattleStartButtonTapped()
        {
            if(_viewModel.ChallengeableCount.IsZero() && _viewModel.AdChallengeableCount.IsZero())
            {
                // 挑戦可能な回数がない場合は何もしない
                AdventBattleWireFrame.ShowEmptyChallengeCountMessage();
                return;
            }

            if (ShouldShowAdvertising())
            {
                var vc = CreateAdConfirmView();
                ViewController.PresentModally(vc);
            }
            else
            {
                DoAsync.Invoke(ViewController.View, async cancellationToken =>
                {
                    // 広告を表示しない場合は、すぐにバトル開始
                    await BattleStart(cancellationToken);
                });
            }
        }

        InAppAdvertisingConfirmViewController CreateAdConfirmView()
        {
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.QuestChallenge,
                String.Empty,//未使用
                1,
                _adventBattleTopModel.AdChallengeableCount.Value,
                () =>
                {
                    DoAsync.Invoke(ViewController.View, async cancellationToken =>
                    {
                        var result = await InAppAdvertisingWireframe.ShowAdAsync(
                            IAARewardFeatureType.QuestChallenge,
                            cancellationToken);
                        if (result is AdResultType.Success) await BattleStart(cancellationToken);
                    });
                });
            return vc;
        }

        bool ShouldShowAdvertising()
        {
            return _adventBattleTopModel.CanChallengeWithAd() &&
                   GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty();
        }

        async UniTask BattleStart(CancellationToken cancellationToken)
        {
            ViewController.View.Interactable = false;
            HomeViewDelegate.ShowTapBlock(false, null, 0f);

            var resultModel = await AdventBattleStartUseCase.StartAdventBattle(
                cancellationToken,
                _mstAdventBattleId,
                _challengeType);

            if (resultModel.ErrorType == AdventBattleErrorType.None)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_012_003);
                AdventBattleWireFrame.TransitInGame();
            }
            else
            {
                // NOTE: 挑戦できない場合は操作できる状態に戻す
                ViewController.View.Interactable = true;
                HomeViewDelegate.HideTapBlock(false, 0f);
                ShowErrorView(resultModel);
            }
        }

        void ShowErrorView(AdventBattleStartUseCaseResultModel resultModel)
        {
            if (resultModel.ErrorType == AdventBattleErrorType.InvalidParty)
            {
                var limitStatusViewModels = resultModel.SpecialRuleStatusModel.LimitStatus
                    .Select(StageLimitStatusViewModelTranslator.TranslateViewModel).ToList();
                var argument = new HomeStageLimitStatusViewController.Argument()
                {
                    PartyName = resultModel.SpecialRuleStatusModel.PartyName,
                    HomeStageLimitStatusViewModels = limitStatusViewModels
                };
                AdventBattleWireFrame.ShowLimitStatusView(ViewController, argument);
            }
            else if (resultModel.ErrorType == AdventBattleErrorType.OutOfTime)
            {
                AdventBattleWireFrame.ShowCloseMessage(null);
            }
            else if (resultModel.ErrorType == AdventBattleErrorType.OverChallengeCount)
            {
                AdventBattleWireFrame.ShowLimitChallengeMessage();
            }
        }


        void IAdventBattleTopViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IAdventBattleTopViewDelegate.OnRewardIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void IAdventBattleTopViewDelegate.OnEscape()
        {
            // チュートリアルダイアログ表示中は画面から出られなくする
            if (_isShowingTutorialDialog)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            if (!ViewController.View.Interactable)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }
            
            HomeViewNavigation.TryPop();
        }

        void IAdventBattleTopViewDelegate.OnSpecialRuleButtonTapped()
        {
            var argument = new InGameSpecialRuleViewController.Argument(
                _mstAdventBattleId,
                InGameContentType.AdventBattle,
                InGameSpecialRuleFromUnitSelectFlag.False);
            AdventBattleWireFrame.ShowSpecialRuleView(ViewController, argument);
        }

        async UniTask ShouldShowAdventBattleTutorialDialog(CancellationToken cancellationToken)
        {
            // チュートリアルダイアログ表示中はバックキーを無効にする
            _isShowingTutorialDialog = true;
            
            using (ViewController.ViewTapGuard())
            {
                // 画面表示のため1f待つ
                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);

                // 降臨バトルに初遷移の場合
                var tutorialFunctionName = TutorialFreePartIdDefinitions.TransitAdventBattle;
                if (!CheckFreePartTutorialCompletedUseCase.CheckFreePartTutorialCompleted(tutorialFunctionName))
                {
                    TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, tutorialFunctionName);
                    await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(cancellationToken, tutorialFunctionName);
                }

                // 協力スコアに初遷移の場合
                var useCaseModel = ShowRaidAdventBattleTutorialDialogUseCase.GetUseCaseModelIfNeeded();

                if (!useCaseModel.IsEmpty())
                {
                    TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(
                        ViewController,
                        useCaseModel.TutorialTipModels);

                    await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(cancellationToken,
                        TutorialFreePartIdDefinitions.TransitRaidAdventBattle);
                }
            }
            
            _isShowingTutorialDialog = false;
        }

        async UniTask PlayHighScoreGaugeScrollAnimation(
            CancellationToken cancellationToken,
            AdventBattleHighScoreGaugeViewModel viewModel)
        {
            // ゲージが伸びない場合はアニメーションを行わない
            if (viewModel.RewardGaugeRateList.IsEmpty()) return;

            // ゲージが伸びるSE
            SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);

            var gaugeRateList = viewModel.RewardGaugeRateList;
            foreach (var gaugeRateModel in gaugeRateList)
            {
                var scrollAnimationPlaying = !gaugeRateModel.HighScoreRewardCellIndex.IsZero();
                await ViewController.PlayHighScoreGaugeScrollAnimation(cancellationToken, gaugeRateModel, scrollAnimationPlaying);
                if (gaugeRateModel.AdventBattleHighScoreRewardObtainedFlag == AdventBattleHighScoreRewardObtainedFlag.True)
                {
                    // 報酬獲得スタンプ時のSE
                    SoundEffectPlayer.Play(SoundEffectId.SSE_061_002);
                }
            }
        }

        async UniTask LoadAndSetupEnemyUnits(
            CancellationToken cancellationToken,
            UnitImageAssetPath displayEnemyUnitFirst,
            UnitImageAssetPath displayEnemyUnitSecond,
            UnitImageAssetPath displayEnemyUnitThird)
        {
            // 敵キャラクターをロード
            var enemyUnitLoadTasks = new List<UniTask>();
            if (!displayEnemyUnitFirst.IsEmpty())
            {
                enemyUnitLoadTasks.Add(UnitImageLoader.Load(cancellationToken, displayEnemyUnitFirst));
            }

            if (!displayEnemyUnitSecond.IsEmpty())
            {
                enemyUnitLoadTasks.Add(UnitImageLoader.Load(cancellationToken, displayEnemyUnitSecond));
            }

            if (!displayEnemyUnitThird.IsEmpty())
            {
                enemyUnitLoadTasks.Add(UnitImageLoader.Load(cancellationToken, displayEnemyUnitThird));
            }

            await UniTask.WhenAll(enemyUnitLoadTasks);

            // 敵キャラクターを表示
            ViewController.SetupEnemyUnitImages(
                displayEnemyUnitFirst,
                displayEnemyUnitSecond,
                displayEnemyUnitThird);
        }

        void LoadAdventBattleTopBackground(KomaBackgroundAssetPath backgroundAssetPath)
        {
            ViewController.SetupAdventBattleTopBackgroundImage(backgroundAssetPath);
        }

        async UniTask<ReceivedAdventBattleScoreRewardsModel> ReceiveRewardsForAdventBattleScore(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId)
        {
            var receivedRewardsModel = await ReceiveAdventBattleScoreRewardsUseCase.ReceiveRewardsForAdventBattleScore(
                cancellationToken,
                mstAdventBattleId);

            return receivedRewardsModel;
        }

        void MonitorAdventBattleRankingStatus()
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                while (true)
                {
                    // NOTE: CancellationTokenがキャンセルされたら処理を終了する
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    // NOTE: １秒間待機（FPSレベルで更新しない）
                    await UniTask.Delay(TimeSpan.FromSeconds(1.0), cancellationToken: cancellationToken);

                    // ランキング状態を取得
                    _calculatingRankings = _adventBattleTopModel.CalculatingRankings(TimeProvider.Now);

                    // 集計状態が変わったらループを抜ける
                    if (!_calculatingRankings)
                    {
                        ViewController.SetupRankingButtonBalloon(_calculatingRankings);
                        break;
                    }
                }
            });
        }

        ReceivedRewardDescription GetAdventBattleReceivedRaidRewardDescription(
            AdventBattleRaidTotalScore raidTotalScore)
        {
            return new ReceivedRewardDescription(ZString.Format("協力スコア {0}達成！！", raidTotalScore.ToDisplayString()));
        }
    }
}
