using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.InGameSpecialRule.Presentation.Presenters;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.InGameSpecialRule.Domain.UseCases;

namespace GLOW.Scenes.InGameSpecialRule.Application
{
    public class InGameSpecialRuleViewInstaller : Installer
    {
        [Inject] InGameSpecialRuleViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<InGameSpecialRuleViewController>();
            Container.BindInterfacesTo<InGameSpecialRulePresenter>().AsCached();
            Container.Bind<InGameSpecialRuleUseCase>().AsCached();
        }
    }
}
