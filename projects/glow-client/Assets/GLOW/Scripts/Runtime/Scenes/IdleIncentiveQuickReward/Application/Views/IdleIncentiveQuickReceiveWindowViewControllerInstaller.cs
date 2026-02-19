using GLOW.Core.Data.Services;
using GLOW.Scenes.IdleIncentiveQuickReward.Domain.UseCases;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Presenters;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views;
using GLOW.Scenes.IdleIncentiveTop.Domain.Evaluator;
using GLOW.Scenes.IdleIncentiveTop.Domain.UseCase;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Application.Views
{
    public sealed class IdleIncentiveQuickReceiveWindowViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<IdleIncentiveQuickReceiveWindowViewController>();
            Container.BindInterfacesTo<IdleIncentiveQuickReceiveWindowPresenter>().AsCached();

            Container.BindInterfacesTo<IdleIncentiveRewardEvaluator>().AsCached();
            
            Container.Bind<GetIdleIncentiveQuickReceiveModelUseCase>().AsCached();
            Container.Bind<GetIdleIncentiveRewardUseCase>().AsCached();
            Container.Bind<ReceiveIdleIncentiveRewardUseCase>().AsCached();
            Container.Bind<UpdateIdleIncentiveAdRewardInfoUseCase>().AsCached();
            Container.BindInterfacesTo<IdleIncentiveService>().AsCached();
        }
    }
}
