using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public class InAppAdvertisingConfirmViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<InAppAdvertisingConfirmViewController>();
        }
    }
}
