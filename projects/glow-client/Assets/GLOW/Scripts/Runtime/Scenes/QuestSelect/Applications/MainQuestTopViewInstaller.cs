using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.MainQuestTop.Presentation;
using GLOW.Scenes.QuestSelect.Domain;
using UIKit.ZenjectBridge;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Applications
{
    public class MainQuestTopViewInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<MainQuestTopViewController>();
            Container.BindInterfacesTo<MainQuestTopPresenter>().AsCached();

            Container.Bind<QuestSelectUseCase>().AsCached();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();
            Container.Bind<HomeStageInfoUseCases>().AsCached();
            Container.Bind<HomeStageInfoViewModelFactory>().AsCached();
            Container.BindInterfacesTo<QuestSelectUseCaseModelItemFactory>().AsCached();
            Container.BindInterfacesTo<QuestDifficultyUseCaseModelItemFactory>().AsCached();
            Container.BindInterfacesTo<NewQuestEvaluator>().AsCached();


        }
    }
}
