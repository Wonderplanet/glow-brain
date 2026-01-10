using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.UnitReceive.Presentation.WireFrame;
using GLOW.Scenes.UserLevelUp.Presentation.Facade;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// 　　53-1-1_勝利画面
    /// 　　53-1-1-1_勝利演出
    /// 　　53-1-1-2_勝利演出時キャラ表示
    /// </summary>
    public class VictoryResultPresenter : IVictoryResultViewDelegate
    {
        const string TapForCloseText = "タップで閉じる";
        const string TapForNextText = "画面をタップ";

        [Inject] VictoryResultViewController ViewController { get; }
        [Inject] VictoryResultViewController.Argument Argument { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IUserLevelUpResultViewFacade UserLevelUpResultViewFacade { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IUnitReceiveWireFrame UnitReceiveWireFrame { get; }

        readonly CancellationTokenSource _resultExpAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _resultRewardAnimationCancellationTokenSource = new ();
        bool _isResultAnimationCompleted;
        bool _isUserLevelUpResultViewDisplayed;
        bool _isUserLevelUpResultViewClosed;
        bool _isArtworkFragmentItemAcquisitionViewDisplayed;
        bool _isArtworkFragmentItemAcquisitionViewClosed;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(VictoryResultPresenter), nameof(OnViewDidLoad));

            ViewController.SetCharacterStandImage(Argument.ViewModel.CharacterStandImageAssetPath);
            ViewController.SetSpeedAttack(Argument.ViewModel.SpeedAttack);
            ViewController.SetUpEventCampaignBalloon(Argument.ViewModel.RemainingEventCampaignTimeSpan);
            ViewController.ShouldShowRetryButton(Argument.ViewModel.RetryAvailableFlag);
            ViewController.SetAcquiredItems(
                Argument.ViewModel.AcquiredPlayerResources,
                Argument.ViewModel.AcquiredPlayerResourcesGroupedByStaminaRap);

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                BackgroundMusicPlayable.PlayWithCrossFade(cancellationToken, BGMAssetKeyDefinitions.BGM_victory_result, 0.2f);

                var resultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, _resultExpAnimationCancellationTokenSource.Token).Token;

                var resultAnimationCanceled = await PlayResultExpAnimation(resultAnimationCancellationToken)
                    .SuppressCancellationThrow();

                cancellationToken.ThrowIfCancellationRequested();

                // 演出スキップしたときは即座に結果を表示する
                if (resultAnimationCanceled)
                {
                    ViewController.SkipSlideInAnimation();
                    ViewController.SetUserExpGain(Argument.ViewModel.UserExpGains.Last());
                }

                await PlaySpeedAttackResultAnimation(cancellationToken);
                await ShowUserLevelUpResultView(cancellationToken);
                await ShowReceivedUnits(cancellationToken);
                await PlayResultRewardAnimation(cancellationToken);

                // 報酬演出完了後のインターバル
                await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);
                await ShowArtworkFragmentItemAcquisitionView(cancellationToken);

                // アニメーション完了後に再挑戦ボタンを有効化
                ViewController.SetInteractableRetryButton(Argument.ViewModel.RetryAvailableFlag);
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(VictoryResultPresenter), nameof(OnViewDidUnload));
        }

        public void OnBackButton()
        {
            OnSkipSelected();

            if (!_isResultAnimationCompleted) return;
            OnCloseSelected();
        }

        public void OnSkipSelected()
        {
            // 経験値の演出までをキャンセルして即座に結果を表示する
            if (!_isResultAnimationCompleted && !_isUserLevelUpResultViewDisplayed)
            {
                _resultExpAnimationCancellationTokenSource.Cancel();
            }

            // 報酬の演出までをキャンセルして即座に結果を表示する
            if (!_isResultAnimationCompleted && _isUserLevelUpResultViewDisplayed)
            {
                _resultRewardAnimationCancellationTokenSource.Cancel();
            }
        }

        public void OnCloseSelected()
        {
            if (!CanProceedAfterAnimations())
            {
                return;
            }

            ViewController.Dismiss(animated:false, completion:Argument.OnViewClosed);
        }

        void IVictoryResultViewDelegate.OnRetrySelected()
        {
            if (!CanProceedAfterAnimations())
            {
                return;
            }

            // スタミナブーストやスタミナ不足の確認を含むリトライ判定
            Argument.OnRetrySelected();
        }

        bool CanProceedAfterAnimations()
        {
            // レベルアップ演出
            if (ExistsUserLevelUpResult() && !_isUserLevelUpResultViewDisplayed)
            {
                return false;
            }
            if (ExistsUserLevelUpResult() && !_isUserLevelUpResultViewClosed)
            {
                return false;
            }

            // 原画のかけら取得演出
            if (ExistsAcquiredArtworkFragmentItem() && !_isArtworkFragmentItemAcquisitionViewDisplayed)
            {
                return false;
            }
            if (ExistsAcquiredArtworkFragmentItem() && !_isArtworkFragmentItemAcquisitionViewClosed)
            {
                return false;
            }

            return true;
        }

        void IVictoryResultViewDelegate.OnIconSelected(PlayerResourceIconViewModel viewModel)
        {

            if (!_isResultAnimationCompleted) return;
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        async UniTask PlaySpeedAttackResultAnimation(CancellationToken cancellationToken)
        {
            if (Argument.ViewModel.SpeedAttack.ClearTime.IsEmpty()) return;

            await ViewController.PlaySpeedAttackResultAnimation(
                Argument.ViewModel.SpeedAttack.IsNewRecord,
                Argument.ViewModel.SpeedAttack.ClearTime,
                1,
                cancellationToken);
        }

        async UniTask PlayResultExpAnimation(CancellationToken cancellationToken)
        {
            ViewController.SetInitialUserExp(Argument.ViewModel.UserExpGains.First());

            await ViewController.PlaySlideInAnimation(cancellationToken);

            foreach (var userExpGain in Argument.ViewModel.UserExpGains)
            {
                if (!userExpGain.NextLevelExp.IsZero() && userExpGain.IsExpGain)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_053_001);
                }

                await ViewController.PlayUserExpGainAnimation(userExpGain, 0.5f, cancellationToken);
                if(userExpGain.IsLevelUp)
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_053_002);
                }
            }

            await UniTask.Delay(TimeSpan.FromSeconds(0.3f), cancellationToken: cancellationToken);
        }

        async UniTask PlayResultRewardAnimation(CancellationToken cancellationToken)
        {
            var rewardResultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _resultRewardAnimationCancellationTokenSource.Token).Token;

            var isHiddenSpeedAttack = Argument.ViewModel.SpeedAttack.SpeedAttackRewards.IsEmpty();

            try
            {
                await ViewController.PlayExpandRewardListAnimation(isHiddenSpeedAttack, rewardResultAnimationCancellationToken);

                if (!isHiddenSpeedAttack)
                {
                    await ViewController.PlaySpeedAttackRewardListAnimation(
                        Argument.ViewModel.SpeedAttack.SpeedAttackRewards, rewardResultAnimationCancellationToken);
                }

                await PlayRewardExpansionAnimation(rewardResultAnimationCancellationToken);
            }
            catch (OperationCanceledException e)
            {
                // 演出スキップしたときは即座に結果を表示する
                SkipSpeedAttackRewardListAnimation();
                SkipResultRewardExpansionAnimation();

                _isResultAnimationCompleted = true;
            }
        }

        async UniTask PlayRewardExpansionAnimation(CancellationToken cancellationToken)
        {
            await ViewController.PlayAcquiredItemsAnimation(
                Argument.ViewModel.AcquiredPlayerResources,
                Argument.ViewModel.AcquiredPlayerResourcesGroupedByStaminaRap,
                cancellationToken);

            ViewController.HideSkipScreenButton();

            var tapLabelText = ExistsAcquiredArtworkFragmentItem() ? TapForNextText : TapForCloseText;
            ViewController.ShowTapLabel(tapLabelText);

            _isResultAnimationCompleted = true;
        }

        async UniTask ShowUserLevelUpResultView(CancellationToken cancellationToken)
        {
            if (_isUserLevelUpResultViewDisplayed) return;

            if (!ExistsUserLevelUpResult())
            {
                _isUserLevelUpResultViewClosed = true;
            }
            else
            {
                UserLevelUpResultViewFacade.ShowWithClosedAction(Argument.ViewModel.UserLevelUpResult, () =>
                {
                    _isUserLevelUpResultViewClosed = true;
                });
            }

            _isUserLevelUpResultViewDisplayed = true;

            await UniTask.WaitUntil(
                () => _isUserLevelUpResultViewDisplayed && _isUserLevelUpResultViewClosed,
                cancellationToken: cancellationToken);
        }

        async UniTask ShowReceivedUnits(CancellationToken cancellationToken)
        {
            var receivedUnitIds = Argument.ViewModel.AcquiredPlayerResources
                .Where(model => model.ResourceType == ResourceType.Unit)
                .Select(model => model.Id)
                .ToList();

            await UnitReceiveWireFrame.ShowReceivedUnits(
                receivedUnitIds,
                ViewController,
                cancellationToken);
        }

        void SkipSpeedAttackRewardListAnimation()
        {
            ViewController.SkipSpeedAttackRewardListAnimation();
        }

        void SkipResultRewardExpansionAnimation()
        {
            ViewController.SkipUserExpGainAnimation();
            ViewController.ShowAcquiredItems(
                Argument.ViewModel.AcquiredPlayerResources,
                Argument.ViewModel.AcquiredPlayerResourcesGroupedByStaminaRap);
            ViewController.HideSkipScreenButton();

            var tapLabelText = ExistsAcquiredArtworkFragmentItem() ? TapForNextText : TapForCloseText;
            ViewController.ShowTapLabel(tapLabelText);
        }

        bool ExistsUserLevelUpResult()
        {
            return !Argument.ViewModel.UserLevelUpResult.IsEmpty();
        }

        async UniTask ShowArtworkFragmentItemAcquisitionView(CancellationToken cancellationToken)
        {
            if (_isArtworkFragmentItemAcquisitionViewDisplayed) return;
            if (!ExistsAcquiredArtworkFragmentItem())
            {
                _isArtworkFragmentItemAcquisitionViewClosed = true;
                return;
            }

            var argument = new ArtworkFragmentAcquisitionViewController.Argument(
                Argument.ViewModel.ArtworkFragmentAcquisitionViewModels[0],
                () =>
                {
                    _isArtworkFragmentItemAcquisitionViewClosed = true;
                    ViewController.ShowTapLabel(TapForCloseText);
                });

            var artworkFragmentItemAcquisitionViewController =
                ViewFactory
                    .Create<ArtworkFragmentAcquisitionViewController,
                        ArtworkFragmentAcquisitionViewController.Argument>(argument);
            ViewController.PresentModally(artworkFragmentItemAcquisitionViewController, false);

            ViewController.HideTapLabel();

            _isArtworkFragmentItemAcquisitionViewDisplayed = true;

            await UniTask.WaitUntil(
                () => _isArtworkFragmentItemAcquisitionViewDisplayed && _isArtworkFragmentItemAcquisitionViewClosed,
                cancellationToken: cancellationToken);
        }

        bool ExistsAcquiredArtworkFragmentItem()
        {
            return Argument.ViewModel.ArtworkFragmentAcquisitionViewModels.Any();
        }
    }
}
