using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpInfo.Presentation.View;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ValueObject;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.ViewModels;
using GLOW.Scenes.PvpNewSeasonStart.Presentation.Views;
using GLOW.Scenes.PvpOpponentDetail.Presentation.Views;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Presenters;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views;
using GLOW.Scenes.PvpRanking.Domain.UseCases;
using GLOW.Scenes.PvpRanking.Presentation.Translators;
using GLOW.Scenes.PvpRanking.Presentation.Views;
using WPFramework.Presentation.Modules;
using GLOW.Scenes.PvpTop.Domain.UseCase;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.PvpTop.Presentation.Calculator;
using GLOW.Scenes.PvpTop.Presentation.Translator;
using GLOW.Scenes.PvpTop.Presentation.View.PvpTicketConfirm;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public class PvpTopPresenter : IPvpTopViewDelegate
    {
        [Inject] PvpWireFrame PvpWireFrame { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] PvpTopViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] PvpTopUseCase PvpTopUseCase { get; }
        [Inject] PvpTopOpponentUseCase PvpTopOpponentUseCase { get; }
        [Inject] PvpRankingUseCase PvpRankingUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IPvpTopTutorialContext PvpTopTutorialContext { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] GetPvpTopRankingStateUseCase GetPvpTopRankingStateUseCase { get; }
        [Inject] PvpStartUseCase PvpStartUseCase { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }

        PvpChallengeStatus _selectedOpponentPvpChallengeStatus = PvpChallengeStatus.Empty;
        PvpTopOpponentViewModel _selectedOpponentViewModel;

        IReadOnlyList<PvpTopOpponentViewModel> _opponentViewModels = new List<PvpTopOpponentViewModel>();
        PvpOpponentRefreshCoolTimeCalculator _coolTimeCalculator;
        CancellationTokenSource _currentCountdownTaskTokenSource;
        ContentSeasonSystemId _sysPvpSeasonId;
        PvpTopUserState _pvpTopUserState;
        ViewableRankingFromCalculatingFlag _isViewableRankingFromCalculating;
        PvpEndAt _pvpEndAt;

        bool _isStartBattle = false;

        CancellationToken _cancellationToken => ViewController.ActualView.GetCancellationTokenOnDestroy();

        void IPvpTopViewDelegate.OnViewDidLoad()
        {
            DoAsync.Invoke(
                _cancellationToken, async (cancellationToken) =>
                {
                    PvpTopUseCaseModel useCaseModel;
                    PvpTopViewModel viewModel;

                    using (ScreenInteractionControl.Lock())
                    {
                        useCaseModel = await PvpTopUseCase.UpdateAndGetModel(cancellationToken);
                        viewModel = PvpTopViewModelTranslator.Translate(useCaseModel);
                        _sysPvpSeasonId = viewModel.SysPvpSeasonId;
                        _opponentViewModels = viewModel.OpponentViewModels;
                        _selectedOpponentViewModel = PvpTopOpponentViewModel.Empty;
                        _selectedOpponentPvpChallengeStatus = viewModel.PvpTopUserState.PvpChallengeStatus;
                        _pvpTopUserState = viewModel.PvpTopUserState;
                        _isViewableRankingFromCalculating = useCaseModel.IsViewableRankingFromCalculating;
                        _pvpEndAt = useCaseModel.PvpEndAt;
                    }
                    
                    ViewController.SetUpView(viewModel);

                    //画面遷移でしても挑戦更新のクールタイムが残っていたら、待つ
                    StartRefreshCoolTimeCountdown(viewModel.PvpOpponentRefreshCoolTime);

                    // 前シーズン結果演出
                    if (!useCaseModel.PvpPreviousSeasonResultAnimationModel.IsEmpty())
                    {
                        await ShowPreviousSeasonResult(useCaseModel, cancellationToken);
                        await ShowNewSeasonStart(
                            useCaseModel.PvpTopUserState.PvpUserRankStatus.PvpRankClassType,
                            useCaseModel.PvpTopUserState.PvpUserRankStatus.ToScoreRankLevel(),
                            cancellationToken);
                    }
                    
                    // 累積ポイント報酬受け取り
                    await ReceivedTotalScoreRewards(useCaseModel.PvpReceivedTotalScoreRewardsModel, cancellationToken);

                    // チュートリアル
                    ScreenInteractionControl.Disable();
                    await PvpTopTutorialContext.DoIfTutorial(() => UniTask.CompletedTask);
                });
        }

        void IPvpTopViewDelegate.OnViewWillAppear()
        {
            //バッジの更新や画面切り替え単位で更新するものを記述する
            ViewController.UpdatePartyName(GetCurrentPartyNameUseCase.GetCurrentPartyName());

            // BGM再生(ホームからバナーなどで直接遷移する場合を考慮
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_quest_content_top);
        }

        void IPvpTopViewDelegate.OnViewDidUnLoad()
        {
            _currentCountdownTaskTokenSource?.Cancel();
            _currentCountdownTaskTokenSource?.Dispose();
            _coolTimeCalculator = null;
        }

        void IPvpTopViewDelegate.OnHelpButtonTapped()
        {
            var functionName = HelpDialogIdDefinitions.PvpTop;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, functionName);
        }

        void IPvpTopViewDelegate.OnOpponentRefreshButtonTapped()
        {
            // 押した瞬間にグレーアウトして押せない状態にする
            ViewController.SetOpponentRefreshButtonGrayOut();

            DoAsync.Invoke(_cancellationToken, ScreenInteractionControl, async (ct) =>
            {
                // リフレッシュapi叩く+クールタイム時間保存
                var model = await PvpTopOpponentUseCase.RefreshMatchUser(ct);

                // _matchUserViewModels更新
                _opponentViewModels = model.OpponentModels
                    .Select(PvpTopViewModelTranslator.TranslatePvpTopOpponentViewModel)
                    .ToList();

                // 選択解除
                _selectedOpponentViewModel = PvpTopOpponentViewModel.Empty;
                SelectOpponent(PvpOpponentNumber.Empty);

                // Top画面の対戦相手更新
                ViewController.SetUpOpponentComponents(_opponentViewModels);

                // クールタイムのUniTaskを作る
                StartRefreshCoolTimeCountdown(model.PvpOpponentRefreshCoolTime);
            });
        }

        void StartRefreshCoolTimeCountdown(PvpOpponentRefreshCoolTime coolTime)
        {
            // 既存のタスクがあればキャンセル
            _currentCountdownTaskTokenSource?.Cancel();
            _currentCountdownTaskTokenSource?.Dispose();
            _coolTimeCalculator = null;

            // 初期化処理
            _currentCountdownTaskTokenSource = new CancellationTokenSource();
            _currentCountdownTaskTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                ViewController.View.destroyCancellationToken
            );
            _coolTimeCalculator = new PvpOpponentRefreshCoolTimeCalculator();
            _coolTimeCalculator.StartCalculate(coolTime);

            // 新しいタスクを開始
            CountdownTask(_coolTimeCalculator, _currentCountdownTaskTokenSource.Token).Forget();
        }

        async UniTask CountdownTask(PvpOpponentRefreshCoolTimeCalculator coolTimeCalculator, CancellationToken cancellationToken)
        {
            while (coolTimeCalculator != null && 0 < coolTimeCalculator.Calculate().Value)
            {
                await UniTask.Delay(System.TimeSpan.FromSeconds(0.1), cancellationToken: cancellationToken);

                //秒数更新
                ViewController.SetOpponentRefreshCoolTime(coolTimeCalculator.Calculate());

                // キャンセル要求があったらループを抜ける
                cancellationToken.ThrowIfCancellationRequested();
            }

            // クールタイム終了時の処理
            ViewController.SetOpponentRefreshCoolTime(new PvpOpponentRefreshCoolTime(0));
        }

        void IPvpTopViewDelegate.OnOpponentTapped(PvpOpponentNumber number)
        {
            if (number.IsEmpty())
            {
                _selectedOpponentViewModel = PvpTopOpponentViewModel.Empty;
                SelectOpponent(PvpOpponentNumber.Empty);
                return;
            }

            //範囲外のときの処理
            if (!number.IsValid() || !number.IsInRange(_opponentViewModels.Count))
            {
                _selectedOpponentViewModel = PvpTopOpponentViewModel.Empty;
                SelectOpponent(PvpOpponentNumber.Empty);
                return;
            }

            _selectedOpponentViewModel = _opponentViewModels[number.ToIndex()];
            SelectOpponent(number);
        }

        bool IPvpTopViewDelegate.IsStartBattle()
        {
            return _isStartBattle;
        }

        void SelectOpponent(PvpOpponentNumber number)
        {
            //選択状態の更新
            ViewController.SelectOpponent(number, _selectedOpponentPvpChallengeStatus);
        }

        void IPvpTopViewDelegate.OnRankingButtonTapped()
        {
            var state = GetPvpTopRankingStateUseCase.GetState(_isViewableRankingFromCalculating);
            if (state.PvpRankingOpeningType == PvpRankingOpeningType.Calculating)
            {
                CommonToastWireFrame.ShowScreenCenterToast("現在ランキング結果の集計中になります\n集計終了までお待ちください");
                return;
            }

            if (state.PvpRankingOpeningType == PvpRankingOpeningType.NotStarted)
            {
                CommonToastWireFrame.ShowScreenCenterToast("現在ランキングを開催しておりません");
                return;
            }

            DoAsync.Invoke(_currentCountdownTaskTokenSource.Token, ScreenInteractionControl, async cancellationToken =>
            {
                var rankingModel = await PvpRankingUseCase.GetPvpRanking(cancellationToken);
                var pvpRankingViewModel = PvpRankingViewModelTranslator.ToViewModel(rankingModel);
                var argument = new PvpRankingViewController.Argument(pvpRankingViewModel);
                var controller = ViewFactory.Create<PvpRankingViewController, PvpRankingViewController.Argument>(argument);
                HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
            });
        }

        void IPvpTopViewDelegate.OnRewardListButtonTapped()
        {
            PvpWireFrame.ShowPvpRewardListView();
        }

        void IPvpTopViewDelegate.OnStageDetailButtonTapped()
        {
            var argument = new PvpInfoViewController.Argument(_sysPvpSeasonId);
            var controller = ViewFactory.Create<PvpInfoViewController, PvpInfoViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void IPvpTopViewDelegate.OnBattleStartTapped()
        {
            if (!_pvpTopUserState.PvpChallengeStatus.CanBeChallengeable())
            {
                //グレーアウトで入ってくることはない前提だが、予防線として記述
                CommonToastWireFrame.ShowScreenCenterToast("現在挑戦できません");
                return;
            }

            if (_pvpTopUserState.PvpChallengeStatus.Type == PvpChallengeType.Ticket)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                // チケットのときは消費確認画面を挟んでからバトル開始
                var argument = new PvpTicketConfirmViewController.Argument(
                    _pvpTopUserState.PvpChallengeStatus.PvpChallengeItemAmount,
                    _pvpTopUserState.PvpChallengeStatus.PvpItemChallengeCost);
                PvpWireFrame.ShowTicketConfirmView(argument, StartBattle, TransitShop, ViewController);
            }
            else
            {
                StartBattle();
            }
        }

        void StartBattle()
        {
            if (_pvpEndAt.Value < TimeProvider.Now)
            {
                PvpWireFrame.BackToHomeAfterPvpEnded();
                return;
            }

            _isStartBattle = true;
            ViewController.View.Interactable = false;
            HomeViewDelegate.ShowTapBlock(false, null, 0f);

            DoAsync.Invoke(_cancellationToken, async (ct) =>
            {
                await PvpStartUseCase.StartPvp(
                    _selectedOpponentViewModel.UserId,
                    _pvpTopUserState.PvpChallengeStatus.Type,
                    ct);
                SoundEffectPlayer.Play(SoundEffectId.SSE_012_003);
                PvpWireFrame.TransitInGame();
            });
        }

        void TransitShop()
        {
            HomeViewControl.OnBasicShopSelected();
        }

        void IPvpTopViewDelegate.OnPartyEditTapped()
        {
            var argument = new HomePartyFormationViewController.Argument(
                _sysPvpSeasonId.ToMasterDataId(),
                InGameContentType.Pvp,
                EventBonusGroupId.Empty,
                MasterDataId.Empty);

            var controller = ViewFactory.Create<
                HomePartyFormationViewController,
                HomePartyFormationViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IPvpTopViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IPvpTopViewDelegate.OnOpponentInfoButtonTapped(int index)
        {
            if (index < 0 || index >= _opponentViewModels.Count)
            {
                return;
            }

            var opponentViewModel = _opponentViewModels.ElementAtOrDefault(index);
            var argument = new PvpOpponentDetailViewController.Argument(opponentViewModel);
            var controller = ViewFactory.Create<
                PvpOpponentDetailViewController,
                PvpOpponentDetailViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        async UniTask ShowPreviousSeasonResult(PvpTopUseCaseModel model, CancellationToken cancellationToken)
        {
            var viewModel = PvpPreviousSeasonResultTranslator.Translate(model.PvpPreviousSeasonResultAnimationModel);
            var argument = new PvpPreviousSeasonResultViewController.Argument(viewModel);
            var viewController = ViewFactory
                .Create<PvpPreviousSeasonResultViewController, PvpPreviousSeasonResultViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
        }

        async UniTask ShowNewSeasonStart(
            PvpRankClassType rankClassType,
            ScoreRankLevel scoreRankLevel,
            CancellationToken cancellationToken)
        {
            var viewModel = new PvpNewSeasonStartViewModel(rankClassType, scoreRankLevel);
            var argument = new PvpNewSeasonStartViewController.Argument(viewModel);
            var viewController = ViewFactory
                .Create<PvpNewSeasonStartViewController, PvpNewSeasonStartViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
        }

        async UniTask ReceivedTotalScoreRewards(
            PvpReceivedTotalScoreRewardsModel rewardModel,
            CancellationToken cancellationToken)
        {
            if(rewardModel.IsEmpty())
            {
                return;
            }
            
            var rewards = rewardModel.TotalScoreRewards;
            var raidTotalScoreRewardIconViewModels =
                rewards.Select(model => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(model))
                    .ToList();
            
            var text = ZString.Format(
                "累計ポイント {0}獲得達成!!",
                rewardModel.ReceivedTotalScore.ToDisplayString());
            await CommonReceiveWireFrame.ShowAsync(
                cancellationToken,
                raidTotalScoreRewardIconViewModels,
                RewardTitle.Default,
                new ReceivedRewardDescription(text));
        }
    }
}
