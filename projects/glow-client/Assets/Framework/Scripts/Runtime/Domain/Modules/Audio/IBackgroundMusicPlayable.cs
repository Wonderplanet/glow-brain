using System.Threading;
using Cysharp.Threading.Tasks;

namespace WPFramework.Domain.Modules
{
    public interface IBackgroundMusicPlayable
    {
        void Play(string assetKey);
        UniTask PlayWithCrossFade(CancellationToken cancellationToken, string assetKey, float duration);
        void Stop();
        UniTask FadeIn(CancellationToken cancellationToken, float duration);
        UniTask FadeOut(CancellationToken cancellationToken, float duration);
    }
}
