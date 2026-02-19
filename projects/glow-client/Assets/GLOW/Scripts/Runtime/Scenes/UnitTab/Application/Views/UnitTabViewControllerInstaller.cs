using GLOW.Scenes.OutpostEnhance.Application.Views;
using GLOW.Scenes.OutpostEnhance.Presentation.Views;
using GLOW.Scenes.PartyFormation.Application.Views;
using GLOW.Scenes.PartyFormation.Presentation.Views;
using GLOW.Scenes.PartyNameEdit.Application.Views;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using GLOW.Scenes.SpecialAttackInfo.Application.Installer;
using GLOW.Scenes.SpecialAttackInfo.Presentation.Views;
using GLOW.Scenes.UnitEnhance.Application.Views;
using GLOW.Scenes.UnitEnhance.Presentation.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceGradeUpDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Application.Views;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Application.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views;
using GLOW.Scenes.UnitList.Application.Vies;
using GLOW.Scenes.UnitList.Domain.UseCases;
using GLOW.Scenes.UnitList.Presentation.Views;
using GLOW.Scenes.UnitTab.Presentation.Presenters;
using GLOW.Scenes.UnitTab.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Scenes.UnitTab.Application.Views
{
    public class UnitTabViewControllerInstaller : Installer
    {
        [InjectOptional] UnitTabViewController.Argument Argument { get; }
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<UnitTabViewController>();
            Container.BindInterfacesTo<UnitTabPresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();
            Container.BindViewFactoryInfo<PartyFormationViewController, PartyFormationViewControllerInstaller>();
            Container.BindViewFactoryInfo<PartyFormationPartyViewController, PartyFormationPageContentControllerInstaller>();
            Container.BindViewFactoryInfo<PartyFormationOneLinePartyViewController, PartyFormationOneLinePartyViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitListViewController, UnitListViewControllerInstaller>();
            Container.Bind<UpdateUnitListFilterUseCase>().AsCached();

            Container.BindViewFactoryInfo<OutpostEnhanceViewController, OutpostEnhanceViewControllerInstaller>();
            Container.BindViewFactoryInfo<PartyNameEditDialogViewController, PartyNameEditDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitViewController, UnitEnhanceViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitLevelUpDialogViewController, UnitLevelUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<SpecialAttackInfoViewController, SpecialAttackInfoViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceRankUpDialogViewController, UnitEnhanceRankUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceGradeUpDialogViewController, UnitEnhanceGradeUpDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<UnitEnhanceRankUpDetailDialogViewController, UnitEnhanceRankUpDetailDialogViewControllerInstaller>();

            if (null != Argument)
            {
                Container.BindInstance(Argument).AsCached();
            }
        }
    }
}
