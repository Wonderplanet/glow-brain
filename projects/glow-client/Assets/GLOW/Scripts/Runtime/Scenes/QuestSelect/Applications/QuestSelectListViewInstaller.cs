using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using GLOW.Scenes.QuestSelect.Domain;
using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.QuestSelectList.Presentation;


namespace GLOW.Scenes.QuestSelectList.Application
{
    public class QuestSelectListViewInstaller : Installer
    {
        [Inject] QuestSelectListViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<QuestSelectListViewController>();
            Container.BindInterfacesTo<QuestSelectListPresenter>().AsCached();

            Container.Bind<QuestSelectUseCase>().AsCached();
            Container.Bind<SelectQuestUseCase>().AsCached();
            Container.BindInterfacesTo<QuestSelectUseCaseModelItemFactory>().AsCached();
            Container.BindInterfacesTo<QuestOpenStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<QuestReleaseCheckSampleFinder>().AsCached();
            Container.BindInterfacesTo<QuestDifficultyUseCaseModelItemFactory>().AsCached();
            Container.BindInterfacesTo<NewQuestEvaluator>().AsCached();
        }
    }
}
