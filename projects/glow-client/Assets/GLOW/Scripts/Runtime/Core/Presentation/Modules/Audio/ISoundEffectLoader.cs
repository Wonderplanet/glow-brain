using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Core.Presentation.Modules.Audio
{
    public interface ISoundEffectLoader
    {
        UniTask Load(CancellationToken cancellationToken, SoundEffectTag tag);
        void Unload(SoundEffectTag tag);
    }
}