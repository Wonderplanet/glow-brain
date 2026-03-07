using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using Zenject;
using UIKit.ZenjectBridge;

namespace GLOW.Scenes.HomeMainKomaSettingItem.Application
{
    public class HomeMainKomaSettingItemViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<HomeMainKomaSettingItemViewController>();
        }
    }
}
