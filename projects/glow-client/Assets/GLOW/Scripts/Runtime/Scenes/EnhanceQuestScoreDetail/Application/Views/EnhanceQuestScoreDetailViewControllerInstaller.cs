using GLOW.Scenes.EnhanceQuestScoreDetail.Domain.UseCases;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Presenters;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Application.Views
{
    public class EnhanceQuestScoreDetailViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EnhanceQuestScoreDetailViewController>();
            Container.BindInterfacesTo<EnhanceQuestScoreDetailPresenter>().AsCached();
            Container.Bind<EnhanceQuestScoreDetailUseCase>().AsCached();
        }
    }
}
