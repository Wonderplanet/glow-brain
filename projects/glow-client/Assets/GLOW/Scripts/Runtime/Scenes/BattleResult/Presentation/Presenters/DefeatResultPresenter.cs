using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1_敗北画面
    /// </summary>
    public class DefeatResultPresenter : IDefeatResultViewDelegate
    {
        [Inject] DefeatResultViewController ViewController { get; }
        [Inject] DefeatResultViewController.Argument Argument { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        readonly CancellationTokenSource _defeatResultAnimationCancellationTokenSource = new ();
        bool _isResultAnimationCompleted;

        void IDefeatResultViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(DefeatResultPresenter), nameof(IDefeatResultViewDelegate.OnViewDidLoad));

            ViewController.Setup(Argument.ViewModel);
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_053_005);

                var resultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                    cancellationToken, _defeatResultAnimationCancellationTokenSource.Token).Token;

                await PlayDefeatResultAnimation(resultAnimationCancellationToken);

                // アニメーション完了後に再挑戦可能であれば再挑戦ボタンを有効化
                ViewController.SetActiveRetryButton(Argument.ViewModel.IsRetryAvailable);
            });
        }

        void IDefeatResultViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(DefeatResultPresenter), nameof(IDefeatResultViewDelegate.OnViewDidUnload));
        }

        void IDefeatResultViewDelegate.OnCloseSelected()
        {
            ViewController.Dismiss(animated:false, completion:Argument.OnViewClosed);
        }

        void IDefeatResultViewDelegate.OnRetrySelected()
        {
            // TODO:スタミナブースト
            // 再挑戦
            Argument.OnRetrySelected();
        }

        async UniTask PlayDefeatResultAnimation(CancellationToken cancellationToken)
        {
            ViewController.PlayDefeatResultAnimation();

            await UniTask.Delay(TimeSpan.FromSeconds(1.0f), cancellationToken: cancellationToken);

            ViewController.ActiveCloseButton();
            ViewController.ActiveCloseText();
        }
    }
}
