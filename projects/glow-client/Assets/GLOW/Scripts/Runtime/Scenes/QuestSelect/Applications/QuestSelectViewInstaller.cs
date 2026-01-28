using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using GLOW.Scenes.QuestSelect.Domain;
using GLOW.Scenes.QuestSelect.Presentation;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Applications
{
    public class QuestSelectViewInstaller : Installer
    {
        [Inject] QuestSelectViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<QuestSelectViewController>();
            Container.BindInterfacesTo<QuestSelectPresenter>().AsCached();

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
