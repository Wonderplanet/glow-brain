using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Modules.Audio;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class AudioInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesAndSelfTo<SystemSoundEffectProvider>().AsCached();
            Container.BindInterfacesTo<SoundEffectLoader>().AsCached();
        }
    }
}
