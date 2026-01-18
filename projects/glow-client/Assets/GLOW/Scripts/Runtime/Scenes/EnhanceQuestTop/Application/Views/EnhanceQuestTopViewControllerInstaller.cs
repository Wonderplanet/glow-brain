using GLOW.Scenes.EventBonusUnitList.Application.Views;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using GLOW.Scenes.EnhanceQuestScoreDetail.Application.Views;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views;
using GLOW.Scenes.EnhanceQuestTop.Domain.Factories;
using GLOW.Scenes.EnhanceQuestTop.Domain.UseCases;
using GLOW.Scenes.EnhanceQuestTop.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.EnhanceQuestTop.Application.Views
{
    public class EnhanceQuestTopViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<EnhanceQuestTopViewController>();
            Container.BindInterfacesTo<EnhanceQuestTopPresenter>().AsCached();
            
            Container.Bind<EnhanceQuestTopUseCase>().AsCached();
            Container.Bind<SetPartyFormationEventBonusUseCase>().AsCached();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();
            Container.Bind<UpdateEnhanceQuestTopUseCase>().AsCached();
            
            Container.BindInterfacesTo<QuestPartyModelFactory>().AsCached();
            Container.BindInterfacesTo<EnhanceQuestModelFactory>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<EventBonusUnitListViewController,
                EventBonusUnitListViewControllerInstaller>();
            Container.BindViewFactoryInfo<EnhanceQuestScoreDetailViewController,
                EnhanceQuestScoreDetailViewControllerInstaller>();
        }
    }
}
