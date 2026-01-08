using System.ComponentModel;
using GLOW.Core.Domain.UseCases;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.UseCase;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.EventQuestTop.Presentation.Presenters;
using GLOW.Scenes.EventQuestTop.Presentation.Translators;
using GLOW.Scenes.EventQuestTop.Presentation.Views;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.InGameSpecialRule.Domain.Evaluator;
using GLOW.Scenes.UnitDetailModal.Application.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Application
{
    public class EventQuestTopViewInstaller : Installer
    {
        [Inject] EventQuestTopViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindInstance(Argument);
            Container.BindViewWithKernal<EventQuestTopViewController>();
            Container.BindInterfacesTo<EventQuestTopPresenter>().AsCached();
            Container.Bind<EventQuestTopUseCase>().AsCached();
            Container.Bind<EventStageSelectUseCase>().AsCached();
            Container.Bind<EventMissionBadgeUseCase>().AsCached();
            Container.Bind<GetCurrentPartyNameUseCase>().AsCached();
            Container.Bind<EventOpenCheckUseCase>().AsCached();
            Container.Bind<EventExchangeShopCheckUseCase>().AsCached();

            Container.BindInterfacesTo<EventQuestTopUseCaseElementModelFactory>().AsCached();
            Container.BindInterfacesTo<ShowStageReleaseAnimationFactory>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentStatusFactory>().AsCached();
            Container.BindViewFactoryInfo<UnitDetailModalViewController, UnitDetailModalViewControllerInstaller>();
            SetUpEventQuestTopUseCaseElementModelFactory();
        }

        void SetUpEventQuestTopUseCaseElementModelFactory()
        {
            Container.BindInterfacesTo<EventQuestTopUnitUseCaseModelFactory>().AsCached();
            Container.BindInterfacesTo<SpeedAttackUseCaseModelFactory>().AsCached();
            Container.BindInterfacesTo<ArtworkFragmentCompleteEvaluator>().AsCached();
            Container.BindInterfacesTo<InGameSpecialRuleEvaluator>().AsCached();
            Container.BindInterfacesTo<UnitListToMstCharacterModelFactory>().AsCached();
            Container.BindInterfacesTo<EventInitialSelectStageFactory>().AsCached();
            Container.Bind<SceneWireFrameUseCase>().AsCached();

        }
    }
}
