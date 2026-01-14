using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public interface IInGameInterruptAnimation
    {
        bool CanSkip { get; }
        InterruptAnimationPriority Priority { get; }
        
        UniTask PlayAsync(CancellationToken cancellationToken);
    }
}