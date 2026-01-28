using GLOW.Core.Data.Services;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.IdleIncentiveTop.Domain.ModelFactories;
using GLOW.Scenes.IdleIncentiveTop.Domain.UseCase;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Presenters;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveTop.Application.Views
{
    internal sealed class IdleIncentiveTopViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<IdleIncentiveRewardEvaluator>().AsCached();
            
            Container.Bind<GetIdleIncentiveTopModelUseCase>().AsCached();
            Container.Bind<GetIdleIncentiveRewardUseCase>().AsCached();
            Container.Bind<ReceiveIdleIncentiveRewardUseCase>().AsCached();
            Container.Bind<GetIdleIncentiveElapsedTimeUseCase>().AsCached();
            Container.Bind<GetIdleIncentiveTopStageUseCase>().AsCached();

            Container.BindViewWithKernal<IdleIncentiveTopViewController>();
            Container.BindInterfacesTo<IdleIncentiveTopPresenter>().AsCached();

            Container.BindInterfacesTo<IdleIncentiveService>().AsCached();
            
            Container.BindInterfacesTo<AutoPlayerSequenceModelFactory>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveTopPlayerUnitModelFactory>().AsCached();
        }
    }
}
