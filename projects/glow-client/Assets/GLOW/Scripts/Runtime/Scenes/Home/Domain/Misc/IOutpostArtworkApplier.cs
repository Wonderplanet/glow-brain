using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Home.Domain.Misc
{
    public interface IOutpostArtworkApplier
    {
        UniTask ApplyOutpostArtwork(CancellationToken cancellationToken);
        void AsyncApplyOutpostArtwork();
    }
}
