using GLOW.Scenes.EmblemDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.UserEmblem.Domain.UseCases;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEmblemDetail.Application
{
    public class EncyclopediaEmblemDetailViewControllerInstaller : Installer
    {
        [Inject] EncyclopediaEmblemDetailViewController.Argument Argument { get; }

        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EncyclopediaEmblemDetailViewController>();
            Container.BindInterfacesTo<EncyclopediaEmblemDetailPresenter>().AsCached();
            Container.Bind<GetEmblemDetailUseCase>().AsCached();
            Container.Bind<ApplyUserEmblemUseCase>().AsCached();
            Container.Bind<ReceiveEncyclopediaFirstCollectionRewardUseCase>().AsCached();

            Container.BindInstance(Argument);
        }
    }
}
