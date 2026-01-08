using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.BattleResult.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// </summary>
    public class VictoryAnimationPresenter : IVictoryAnimationViewDelegate
    {
        const int CloseBlockTime = 2000;

        [Inject] VictoryAnimationViewController ViewController { get; }
        [Inject] VictoryAnimationViewController.Argument Argument { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();

        bool _canClose;

        bool _isClosed;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(VictoryAnimationPresenter), nameof(OnViewDidLoad));

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                await UniTask.Delay(CloseBlockTime, cancellationToken: cancellationToken);
                _canClose = true;
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(VictoryAnimationPresenter), nameof(OnViewDidUnload));
        }

        public void OnAnimationCompleted()
        {
            Close(CancellationToken).Forget();
        }

        public void OnCloseSelected()
        {
            if (!_canClose) return;

            Close(CancellationToken).Forget();
        }

        async UniTask Close(CancellationToken cancellationToken)
        {
            if (_isClosed) return;
            _isClosed = true;

            await ViewController.PlayCloseAnimation(cancellationToken);
            ViewController.Dismiss(animated:false, completion:Argument.OnViewClosed);
        }
    }
}
