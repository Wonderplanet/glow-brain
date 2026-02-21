using WPFramework.Domain.Modules;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public class UISoundEffector
    {
        [Inject] ISoundEffectPlayable SoundEffectPlayable { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        public static UISoundEffector Main { get; private set; }

        public UISoundEffector()
        {
            Main = this;
        }

        public void Play(string assetKey)
        {
            SoundEffectPlayable.Play(assetKey);
        }

        public void PlaySeTapYes()
        {
            SystemSoundEffectProvider.PlaySeTapYes();
        }

        public void PlaySeEscape()
        {
            SystemSoundEffectProvider.PlaySeEscape();
        }

        public void Stop(string assetKey)
        {
            SoundEffectPlayable.Stop(assetKey);
        }
        
        public void Stop()
        {
            SoundEffectPlayable.Stop();
        }
    }
}
