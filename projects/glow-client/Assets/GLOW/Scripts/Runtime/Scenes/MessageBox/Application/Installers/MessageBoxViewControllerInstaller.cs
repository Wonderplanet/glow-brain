using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Services;
using GLOW.Scenes.MessageBox.Domain.Evaluator;
using GLOW.Scenes.MessageBox.Presentation.Presenter;
using GLOW.Scenes.MessageBox.Domain.UseCase;
using GLOW.Scenes.MessageBox.Presentation.View;
using GLOW.Scenes.MessageBoxDetail.Presentation.Control;
using GLOW.Scenes.MessageBoxDetail.Application.Installers;
using GLOW.Scenes.MessageBoxDetail.Presentation.View;
using GLOW.Scripts.Runtime.Scenes.MessageBox.Presentation.Presenter;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.MessageBox.Application.Installers
{
    public class MessageBoxViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<MessageBoxViewController>();
            Container.BindInterfacesTo<MessageBoxPresenter>().AsCached();

            Container.Bind<GetMessageListUseCase>().AsCached();
            Container.Bind<ReceiveMessageRewardUseCase>().AsCached();
            Container.Bind<CanReceiveMessageUseCase>().AsCached();
            Container.Bind<OpenMessageUseCase>().AsCached();
            
            Container.BindInterfacesTo<MessageExpiryEvaluator>().AsCached();
            Container.BindInterfacesTo<UnreceivedMessageWireframe>().AsCached();
            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindInterfacesTo<MessageBoxDetailViewControl>().AsCached();
            Container.BindViewFactoryInfo<MessageBoxDetailViewController, MessageBoxDetailViewControllerInstaller>();
            Container.BindViewFactoryInfo<MessageBoxDetailWithRewardViewController, MessageBoxDetailWithRewardViewControllerInstaller>();
        }
    }
}
