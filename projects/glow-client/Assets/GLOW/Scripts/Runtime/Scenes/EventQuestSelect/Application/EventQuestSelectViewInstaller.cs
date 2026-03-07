using Zenject;
using UIKit.ZenjectBridge;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.Evaluator;
using GLOW.Scenes.EventQuestSelect.Domain.Factory;
using GLOW.Scenes.EventQuestSelect.Domain.UseCase;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.QuestSelect.Domain;
using WPFramework.Application.Modules;

namespace GLOW.Scenes.EventQuestSelect.Application
{
    public class EventQuestSelectViewInstaller : Installer
    {
        [Inject] EventQuestSelectViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<EventQuestSelectViewController>();
            Container.BindInterfacesTo<EventQuestSelectPresenter>().AsCached();
            Container.BindInterfacesTo<EventQuestListUseCaseElementModelFactory>().AsCached();
            Container.BindInterfacesTo<ReleaseRequiredMstQuestFactory>().AsCached();
            Container.BindInterfacesTo<QuestOpenStatusEvaluator>().AsCached();
            Container.BindInterfacesTo<QuestReleaseCheckSampleFinder>().AsCached();
            Container.BindInterfacesTo<NewQuestEvaluator>().AsCached();
            Container.BindInterfacesTo<AdventBattleOpenStatusEvaluator>().AsCached();
            Container.Bind<EventQuestListUseCase>().AsCached();
            Container.Bind<EventOpenCheckUseCase>().AsCached();
            Container.Bind<EventQuestOpenCheckUseCase>().AsCached();
            Container.Bind<EventQuestBadgeUseCase>().AsCached();
            Container.Bind<OpenAdventBattleGetUseCase>().AsCached();
            Container.Bind<EventExchangeShopCheckUseCase>().AsCached();
            Container.BindInterfacesTo<EventQuestBackGroundLoader>().AsCached();
        }
    }
}
