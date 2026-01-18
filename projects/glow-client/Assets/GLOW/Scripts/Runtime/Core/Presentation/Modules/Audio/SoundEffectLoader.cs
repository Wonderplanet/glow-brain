using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Modules.Audio
{
    public class SoundEffectLoader : ISoundEffectLoader
    {
        [Inject] ISoundEffectManagement SoundEffectManagement { get; }

        public async UniTask Load(CancellationToken cancellationToken, SoundEffectTag tag)
        {
            await SoundEffectManagement.Load(cancellationToken, GetSoundEffectAssetKeys(tag));
        }

        public void Unload(SoundEffectTag tag)
        {
            SoundEffectManagement.Unload(GetSoundEffectAssetKeys(tag));
        }
        
        string[] GetSoundEffectAssetKeys(SoundEffectTag tag)
        {
            return SoundEffects.Dictionary.Values
                .Where(se => se.Tag == tag)
                .Select(se => (string)se.AssetKey.Value)
                .ToArray();
        }
    }
}