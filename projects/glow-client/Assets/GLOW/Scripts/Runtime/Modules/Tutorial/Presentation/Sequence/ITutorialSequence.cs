using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public interface ITutorialSequence
    {
        UniTask Play(CancellationToken cancellationToken);
    }
}
