using System;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.Views;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.InGame.Application.Installers
{
    public class InGameSceneInstaller : MonoInstaller<InGameSceneInstaller>
    {
        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(InGameSceneInstaller), nameof(InstallBindings));

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<InGameViewController, InGameViewControllerInstaller>();

            Container.BindInterfacesTo<RandomProvider>().AsCached();
            Container.BindInterfacesTo<CoordinateConverter>().AsCached();
            Container.BindInterfacesAndSelfTo<ViewCoordinateConverter>().AsCached();

            var timeScaleController = new TimeScaleController(new TimeScaleApplier());
            Container.Bind<ITimeScaleController>().FromInstance(timeScaleController);
            Container.Bind<IDisposable>().FromInstance(timeScaleController);

            Container.BindInterfacesTo<UnitImageLoader>().AsCached();
            Container.BindInterfacesTo<UnitAttackViewInfoSetLoader>().AsCached();
            Container.BindInterfacesTo<OutpostViewInfoLoader>().AsCached();
            Container.BindInterfacesTo<DefenseTargetImageLoader>().AsCached();
            Container.BindInterfacesTo<InGameGimmickObjectImageLoader>().AsCached();
            Container.BindInterfacesTo<FontAssetClearExecutor>().AsCached();
            Container.BindInterfacesTo<FontAssetLoader>().AsCached();

            Container.Bind<PrefabFactory<OutpostView>>().AsCached();
            Container.Bind<PrefabFactory<OutpostSpriteView>>().AsCached();
            Container.Bind<PrefabFactory<FieldUnitView>>().AsCached();
            Container.Bind<PrefabFactory<FieldSpecialUnitView>>().AsCached();
            Container.Bind<PrefabFactory<DefenseTargetView>>().AsCached();
            Container.Bind<PrefabFactory<InGameGimmickObjectView>>().AsCached();
            Container.Bind<PrefabFactory<PlacedItemView>>().AsCached();
        }
    }
}
