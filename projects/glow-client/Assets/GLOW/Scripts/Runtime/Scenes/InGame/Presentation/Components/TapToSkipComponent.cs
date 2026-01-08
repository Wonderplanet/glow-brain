using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class TapToSkipComponent : UIObject
    {
        CancellationTokenSource _delayCancellationTokenSource;

        protected override void OnDestroy()
        {
            base.OnDestroy();

            _delayCancellationTokenSource?.Cancel();
            _delayCancellationTokenSource?.Dispose();
        }

        public void Show(float delay)
        {
            _delayCancellationTokenSource?.Cancel();
            _delayCancellationTokenSource?.Dispose();
            _delayCancellationTokenSource = null;

            if (delay == 0f)
            {
                Hidden = false;
                return;
            }

            _delayCancellationTokenSource = new CancellationTokenSource();

            var linkedCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                this.GetCancellationTokenOnDestroy(), _delayCancellationTokenSource.Token).Token;

            DoAsync.Invoke(linkedCancellationToken, async cancellationToken =>
            {
                await UniTask.Delay(TimeSpan.FromSeconds(delay), cancellationToken: cancellationToken);
                Hidden = false;
            });
        }

        public void Hide()
        {
            _delayCancellationTokenSource?.Cancel();
            _delayCancellationTokenSource?.Dispose();
            _delayCancellationTokenSource = null;

            Hidden = true;
        }
    }
}
