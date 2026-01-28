using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Presenters
{
    /// <summary>
    /// 41_メインクエスト・ステージ
    /// 　41-2_ステージ共通
    /// 　　41-2-10_原画のかけら獲得ダイアログ
    /// </summary>
    public class ArtworkFragmentAcquisitionPresenter : IArtworkFragmentAcquisitionViewDelegate
    {
        [Inject] ArtworkFragmentAcquisitionViewController ViewController { get; }
        [Inject] ArtworkFragmentAcquisitionViewController.Argument Argument { get; }
        [InjectOptional] IInGameResultFreePartTutorialContext InGameResultFreePartTutorialContext { get; }

        readonly CancellationTokenSource _artworkFragmentAnimationCancellationTokenSource = new CancellationTokenSource();
        readonly CancellationTokenSource _artworkCompleteAnimationCancellationTokenSource = new CancellationTokenSource();

        bool _isArtworkFragmentAnimationCompleted;
        bool _isArtworkCompleteAnimationCompleted;
        bool _isAnimationEnded;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ArtworkFragmentAcquisitionPresenter), nameof(OnViewDidLoad));

            ViewController.Setup(Argument.ViewModel);
            ViewController.SetSkipButtonAction(SkipAnimation);

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);

                await PlayArtworkFragmentAnimation(cancellationToken);
                await PlayArtworkCompleteAnimation(cancellationToken);
                EndAnimation();
                
                // チュートリアルコンテキストがなければ処理を抜ける(インゲーム以外はnullになる)
                if (InGameResultFreePartTutorialContext == null) return;
                
                // チュートリアルがあれば開始する
                await InGameResultFreePartTutorialContext.DoIfTutorial(() => UniTask.CompletedTask);
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(ArtworkFragmentAcquisitionPresenter), nameof(OnViewDidUnload));
        }

        public void OnCloseSelected()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
            ViewController.Dismiss(completion:Argument.OnViewClosed);
        }

        public void OnBackButton()
        {
            if (_isAnimationEnded) OnCloseSelected();

            SkipAnimation();
        }

        async UniTask PlayArtworkFragmentAnimation(CancellationToken cancellationToken)
        {
            var fragmentAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _artworkFragmentAnimationCancellationTokenSource.Token).Token;

            var fragmentAnimationCanceled = await
                ViewController.PlayArtworkFragmentAnimation(Argument.ViewModel.AcquiredArtworkFragmentIds, fragmentAnimationCancellationToken)
                    .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            if (fragmentAnimationCanceled)
            {
                _isArtworkFragmentAnimationCompleted = true;
                ViewController.SkipArtworkFragmentAnimation(Argument.ViewModel.AcquiredArtworkFragmentIds);
            }

            _isArtworkFragmentAnimationCompleted = true;
        }

        async UniTask PlayArtworkCompleteAnimation(CancellationToken cancellationToken)
        {
            if (!Argument.ViewModel.IsCompleted)
            {
                return;
            }

            var completeAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _artworkCompleteAnimationCancellationTokenSource.Token).Token;

            var completeAnimationCanceled = await ViewController.PlayArtworkCompleteAnimation(Argument.ViewModel.AddHp, completeAnimationCancellationToken)
                .SuppressCancellationThrow();
            cancellationToken.ThrowIfCancellationRequested();

            if (completeAnimationCanceled)
            {
                _isArtworkCompleteAnimationCompleted = true;
                ViewController.SkipArtworkCompleteAnimation();
            }

            _isArtworkCompleteAnimationCompleted = true;
        }

        void SkipAnimation()
        {
            if (!_isArtworkFragmentAnimationCompleted && !_isArtworkCompleteAnimationCompleted)
            {
                _artworkFragmentAnimationCancellationTokenSource?.Cancel();
            }
            else if (_isArtworkFragmentAnimationCompleted && !_isArtworkCompleteAnimationCompleted)
            {
                _artworkCompleteAnimationCancellationTokenSource?.Cancel();
            }
        }

        void EndAnimation()
        {
            ViewController.EndAnimation();
            _isAnimationEnded = true;
        }
    }
}
