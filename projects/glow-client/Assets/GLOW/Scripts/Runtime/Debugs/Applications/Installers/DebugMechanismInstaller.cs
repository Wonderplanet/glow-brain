#if GLOW_DEBUG
using GLOW.Core.Domain.Calculator;
using GLOW.Debugs.AdminDebug.Presentation;
using GLOW.Debugs.Command.Data.Repositories;
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Command.Presentations.Views;
using GLOW.Debugs.Command.Presentations.Views.DebugAssetExistsCheckerView;
using GLOW.Debugs.Home.Domain.UseCases;
using GLOW.Debugs.InGame.Data.Repositories;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.Reporter;
using GLOW.Debugs.AdminDebug.Data.DataStores;
using GLOW.Debugs.AdminDebug.Data.Services;
using GLOW.Debugs.AdminDebug.Domain.UseCases;
using GLOW.Debugs.DebugGrid.Presentation.Views;
using GLOW.Debugs.Home.Presentation.DebugCommands;
using GLOW.Scenes.DebugMstUnitStatus.Application;
using GLOW.Scenes.DebugStageDetail.Application;
#endif //GLOW_DEBUG

#if DEBUG
using GLOW.Debugs.Environment.Data.Services;
using WPFramework.Debugs.Environment;
using WPFramework.Debugs.Environment.Data.DataStores;
using WPFramework.Debugs.Environment.Data.Repositories;
using WPFramework.Debugs.Environment.Domain.UseCases;
using WPFramework.Debugs.Environment.Presentation.Presenters;
using WPFramework.Debugs.Environment.Presentation.Views;
using WPFramework.Debugs.Profiler;
#endif //DEBUG

using GLOW.Debugs.Command.Presentations.Views.DebugMstUnitStatusView;
using GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView;
using GLOW.Scenes.DebugStageDetail.Domain;
using UnityEngine;
using WPFramework.Application.Modules;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Debugs.Applications.Installers
{
    [CreateAssetMenu(fileName = "DebugMechanismInstaller", menuName = "Installers/GLOW/DebugMechanismInstaller")]
    public class DebugMechanismInstaller : ScriptableObjectInstaller<DebugMechanismInstaller>
    {
#if GLOW_DEBUG
        [SerializeField] DebugSystemUsageProfile _debugSystemUsageProfile;
        [SerializeField] DebugCommandView _debugCommandView;
        [SerializeField] AdminDebugView _adminDebugView;
        [SerializeField] DebugGridView _debugGridView;
        [SerializeField] DebugLogViewerView _debugLogViewerView;
        [SerializeField] DebugEnvironmentSelectView _debugEnvironmentSelectView;

        public override void InstallBindings()
        {
            ApplicationLog.Log(nameof(DebugMechanismInstaller), nameof(InstallBindings));

            Container.Bind<DebugSystemUsageProfile>().FromComponentInNewPrefab(_debugSystemUsageProfile).AsCached().NonLazy();

            Container.BindInterfacesTo<DebugAdvertisingRepository>().AsCached();
            Container.Bind<DebugCommandUseCases>().AsCached();
            Container.BindInterfacesTo<DebugCommandPresenter>().AsCached();
            Container
                .Bind<DebugCommandView>()
                .FromComponentInNewPrefab(_debugCommandView)
                .AsTransient();
            Container
                .Bind<DebugGridView>()
                .FromComponentInNewPrefab(_debugGridView)
                .AsTransient();
            Container
                .Bind<DebugLogViewerView>()
                .FromComponentInNewPrefab(_debugLogViewerView)
                .AsTransient();
            Container
                .Bind<DebugEnvironmentSelectView>()
                .FromComponentInNewPrefab(_debugEnvironmentSelectView)
                .AsTransient();

            Container.BindViewFactoryInfo<DebugAssetExistsCheckerViewController, DebugAssetExistsCheckerViewInstaller>();
            Container.BindInterfacesAndSelfTo<DebugCommandActivator>().AsCached().NonLazy();

            Container.BindInterfacesTo<DebugEnvironmentTargetStreamingAssetsDataStore>().AsCached();
            Container.BindInterfacesTo<DebugEnvironmentPlayerPrefsDataStore>().AsCached();
            Container.BindInterfacesTo<DebugEnvironmentRepository>().AsCached();

            Container.BindInterfacesTo<DebugEnvironmentService>().AsCached();
            Container.Bind<DebugEnvironmentUseCases>().AsCached();
            Container.BindInterfacesTo<DebugEnvironmentSelectPresenter>().AsCached();
            Container.BindInterfacesAndSelfTo<DebugEnvironmentSelector>().AsCached();

            Container.BindViewFactoryInfo<
                DebugEnvironmentSpecifiedDomainViewController,
                DebugEnvironmentSpecifiedDomainViewControllerInstaller>();

            Container.BindInterfacesTo<DebugReporter>().AsCached();

            // Home
            Container.Bind<GetAllStagesDebugUseCase>().AsCached();
            Container.Bind<DebugSetMyPartyToPvpOpponentUseCase>().AsCached();
            Container.Bind<DebugMstUnitStatusUseCase>().AsCached();
            Container.Bind<DebugGetMstUnitTemporaryParameterUseCase>().AsCached();
            Container.Bind<DebugSetMstUnitTemporaryParameterUseCase>().AsCached();
            Container.Bind<DebugUpdateSummonTemporaryParameterUseCase>().AsCached();
            Container.Bind<DebugGetUnitTemporaryParameterUseCase>().AsCached();
            Container.Bind<UnitTemporaryParameterDebugCommand>().AsCached();
            Container.BindInterfacesTo<DebugMstUnitAttackStatusModelFactory>().AsCached();
            Container.BindInterfacesTo<DebugMstUnitLevelStatusModelFactory>().AsCached();
            Container.BindInterfacesTo<DebugMstUnitSpecialUnitSpecialParamModelFactory>().AsCached();
            Container.BindInterfacesTo<UnitStatusCalculator>().AsCached();
            Container.BindViewFactoryInfo<
                DebugMstUnitStatusViewController,
                DebugMstUnitStatusViewInstaller>();
            Container.BindViewFactoryInfo<
                DebugStageDetailViewController,
                DebugStageDetailViewInstaller>();
            Container.BindInterfacesTo<DebugStageDetailPresenter>().AsCached();
            Container.Bind<DebugStageSummaryUseCase>().AsCached();
            Container.Bind<DebugStageDetailUseCase>().AsCached();
            Container.Bind<PvpDebugStageDetailModelFactory>().AsCached();
            Container.Bind<AdventBattleDebugStageDetailModelFactory>().AsCached();

            // InGame
            Container.BindInterfacesTo<InGameDebugSettingRepository>().AsCached();
            Container.Bind<DisableInGameApiCallDebugUseCase>().AsCached();


            BindAdminDebug();
        }

        void BindAdminDebug()
        {
            Container.BindInterfacesTo<AdminDebugViewPresenter>().AsCached();
            Container
                .Bind<AdminDebugView>()
                .FromComponentInNewPrefab(_adminDebugView)
                .AsTransient();
            Container.Bind<AdminDebugUseCases>().AsCached();
            Container.BindInterfacesTo<AdminDebugMenuService>().AsCached();
            Container.Bind<AdminDebugMenuApi>().AsCached();
        }
#else
        public override void InstallBindings()
        {
        }
#endif
    }
}
