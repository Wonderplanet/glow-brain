using GLOW.Core.Presentation.Modules.Audio;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Modules
{
    public class SystemSoundEffectProvider : ISystemSoundEffectProvider
    {
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }

        void ISystemSoundEffectProvider.PlaySeTap()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
        }

        void ISystemSoundEffectProvider.PlaySeTapYes()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
        }

        public void PlaySeEscape()
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
        }

        public void PlaySeIconAppearEffect()
        {
        }
    }
}
