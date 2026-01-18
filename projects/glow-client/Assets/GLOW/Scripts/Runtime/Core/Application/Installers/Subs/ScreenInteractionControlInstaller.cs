using GLOW.Core.Presentation.Views.Interaction;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class ScreenInteractionControlInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: スクリーンインタラクション
            Container.BindInterfacesAndSelfTo<ScreenInteractionControl<ScreenActivityIndicatorViewController>>().AsCached();
        }
    }
}
