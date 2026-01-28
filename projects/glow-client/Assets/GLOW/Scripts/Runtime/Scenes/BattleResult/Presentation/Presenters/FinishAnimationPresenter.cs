using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10_降臨バトル専用バトルリザルト画面
    ///
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-6-1_ コイン獲得クエスト専用バトルリザルト演出、バトル終了時演出など
    /// </summary>
    public class FinishAnimationPresenter : IFinishAnimationViewDelegate
    {
        const int CloseBlockTime = 2000;

        [Inject] FinishAnimationViewController ViewController { get; }
        [Inject] FinishAnimationViewController.Argument Argument { get; }
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        CancellationToken CancellationToken => ViewController.View.GetCancellationTokenOnDestroy();

        bool _canClose;

        bool _isClosed;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(FinishAnimationPresenter), nameof(OnViewDidLoad));

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_002);

            DoAsync.Invoke(CancellationToken, async cancellationToken =>
            {
                await UniTask.Delay(CloseBlockTime, cancellationToken: cancellationToken);
                _canClose = true;
            });
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(FinishAnimationPresenter), nameof(OnViewDidUnload));
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
