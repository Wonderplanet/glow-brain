using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.UserLevelUp.Presentation.Presenter
{
    public class UserLevelUpResultPresenter : IUserLevelUpResultViewDelegate
    {
        [Inject] UserLevelUpViewController ViewController { get; }
        [Inject] UserLevelUpViewController.Argument Argument { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        readonly CancellationTokenSource _animationCancellationTokenSource = new ();

        bool _isCompletedAnimation;

        bool _isSoundPlayed;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(UserLevelUpResultPresenter), nameof(OnViewDidLoad));

            _isCompletedAnimation = false;
            _isSoundPlayed = false;

            ViewController.SetupUserLevelNumber(Argument.ViewModel.NextUserLevel, Argument.ViewModel.IsLevelMax);
            ViewController.ShowSkipButton();
            ViewController.HideCloseButton();

            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                var resultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, _animationCancellationTokenSource.Token).Token;

                var resultAnimationCanceled = await PlayUserLevelUpAnimation(resultAnimationCancellationToken)
                    .SuppressCancellationThrow();

                cancellationToken.ThrowIfCancellationRequested();

                // 演出スキップしたときは即座に結果を表示する
                if (resultAnimationCanceled)
                {
                    SkipUserLevelUpAnimation();
                    _isCompletedAnimation = true;
                    _isSoundPlayed = true;
                }
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(UserLevelUpResultPresenter), nameof(OnViewDidUnload));
        }

        public void OnBackButton()
        {
            if(!_isCompletedAnimation) OnSkipSelected();
            else OnCloseSelected();
        }

        public void OnSkipSelected()
        {
            ApplicationLog.Log(nameof(UserLevelUpResultPresenter), nameof(OnSkipSelected));

            // 演出をキャンセルして即座に結果を表示する
            if (!_isCompletedAnimation)
            {
                _animationCancellationTokenSource.Cancel();
            }
        }

        public void OnCloseSelected()
        {
            ApplicationLog.Log(nameof(UserLevelUpResultPresenter), nameof(OnCloseSelected));

            // 演出再生終了かスキップしないと閉じるボタンが表示されないのでこのままで大丈夫
            ViewController.Dismiss(animated: true, completion: Argument.OnViewClosed);
        }

        async UniTask PlayUserLevelUpAnimation(CancellationToken cancellationToken)
        {
            await ViewController.PlayLevelUpEffectAnimation(cancellationToken);
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);

            _isSoundPlayed = true;
            await ViewController.PlayUserLevelLabel(cancellationToken);
            await ViewController.PlayMaxStaminaUpLabel(
                Argument.ViewModel.BeforeMaxStamina,
                Argument.ViewModel.AfterMaxStamina,
                cancellationToken);
            await ViewController.PlayMaxStaminaDifference(
                Argument.ViewModel.BeforeMaxStamina,
                Argument.ViewModel.AfterMaxStamina,
                cancellationToken);

            if (!Argument.ViewModel.PlayerResourceIconViewModels.IsEmpty())
            {
                await ViewController.PlayRewardLabelVisible(cancellationToken);
                await ViewController.PlayRewardItemAnimation(Argument.ViewModel.PlayerResourceIconViewModels, cancellationToken);
            }

            await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken:cancellationToken);
            ViewController.HideSkipButton();
            ViewController.ShowCloseButton();
            await ViewController.PlayCloseTextVisible(cancellationToken);
        }

        void SkipUserLevelUpAnimation()
        {
            ViewController.ShowUserLevel();
            if (!_isSoundPlayed)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);
                _isSoundPlayed = true;
            }
            ViewController.ShowMaxStaminaComponent(
                Argument.ViewModel.BeforeMaxStamina,
                Argument.ViewModel.AfterMaxStamina);
            if (!Argument.ViewModel.PlayerResourceIconViewModels.IsEmpty())
            {
                ViewController.ShowRewardLabel();
                ViewController.ShowRewardList(Argument.ViewModel.PlayerResourceIconViewModels);
            }
            ViewController.ShowCloseLabel();
            ViewController.SkipLevelUpEffectAnimation();
            ViewController.HideSkipButton();
            ViewController.ShowCloseButton();
        }

        void IUserLevelUpResultViewDelegate.OnIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }
    }
}
