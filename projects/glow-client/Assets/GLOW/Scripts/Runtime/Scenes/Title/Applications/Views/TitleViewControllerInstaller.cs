using GLOW.Core.Data.Services;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.AgreementDialog.Application.Installers;
using GLOW.Scenes.AgreementDialog.Presentation.Views;
using GLOW.Scenes.AnnouncementWindow.Application.Installers;
using GLOW.Scenes.AnnouncementWindow.Domain.UseCase;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.AnnouncementWindow.Presentation.View;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Application.Views;
using GLOW.Scenes.AppTrackingTransparencyConfirm.Presentation.Views;
using GLOW.Scenes.AssetDownloadNotice.Application.Views;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using GLOW.Scenes.DataRepair.Application;
using GLOW.Scenes.DataRepair.Presentation;
using GLOW.Scenes.GachaList.Domain.Applier;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.Inquiry.Application.Views;
using GLOW.Scenes.Inquiry.Domain.UseCases;
using GLOW.Scenes.Inquiry.Presentation.Presenter;
using GLOW.Scenes.Inquiry.Presentation.View;
using GLOW.Scenes.LinkBnIdWebViewDialog.Application.Views;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using GLOW.Scenes.Login.Domain.UseCase;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.TermsOfService.Application.Installers;
using GLOW.Scenes.TermsOfService.Presentation.Views;
using GLOW.Scenes.Title.Domains.UseCase;
using GLOW.Scenes.Title.Presentations.Presenters;
using GLOW.Scenes.Title.Presentations.Views;
using GLOW.Scenes.Title.Presentations.WireFrame;
using GLOW.Scenes.TitleLinkBnIdDialog.Application.Views;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.TitleMenu.Application;
using GLOW.Scenes.TitleMenu.Presentation;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Application.Installers;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.UserNameEdit.Application.Views;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using UIKit.ZenjectBridge;
using WPFramework.Application.Modules;
using WPFramework.Data.Services;
using Zenject;

namespace GLOW.Scenes.Title.Applications
{
    public class TitleViewControllerInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindViewWithKernal<TitleViewController>();
            Container.BindInterfacesTo<TitlePresenter>().AsCached();
            Container.Bind<TitleWireFrame>().AsCached();
            Container.BindInterfacesTo<PeriodOutsideExceptionWireframe>().AsCached();
            Container.BindInterfacesTo<SessionResumePresenter>().AsCached();

            Container.BindInterfacesTo<ViewFactory>().AsCached();

            InstallTitle();
            InstallSessionResume();
            InstallAgreement();
            InstallTitleMenu();

            InstallAnnouncement();
            InstallPass();
            InstallInquiry();

            InstallTutorial();
        }

        void InstallTutorial()
        {
            Container.Bind<TutorialIntroductionStageStartUseCase>().AsCached();
            Container.Bind<ShouldTutorialDownloadUseCase>().AsCached();
            Container.Bind<ShouldTutorialSetNameUseCase>().AsCached();
            Container.Bind<TutorialBackGroundDownloadUseCase>().AsCached();
            Container.BindInterfacesTo<TutorialStatusApplier>().AsCached();

            Container.BindInterfacesTo<BattleEndConditionFactory>().AsCached();
            Container.Bind<ProgressTutorialStatusUseCase>().AsCached();
            
#if GLOW_DEBUG
            Container.Bind<CompleteFreePartTutorialUseCase>().AsCached();
            Container.BindInterfacesTo<UserTutorialFreePartModelsApplier>().AsCached();
#endif
        }

        void InstallTitleMenu()
        {
            Container.BindViewFactoryInfo<TitleMenuViewController, TitleMenuViewInstaller>();
            Container.BindViewFactoryInfo<DataRepairViewController, DataRepairViewInstaller>();
            Container.BindViewFactoryInfo<TitleLinkBnIdDialogViewController, TitleLinkBnIdDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<
                TitleBnIdLinkageResultDialogViewController,
                TitleBnIdLinkageResultDialogViewControllerInstaller>();
            Container.BindViewFactoryInfo<LinkBnIdWebViewDialogViewController, LinkBnIdWebViewDialogViewControllerInstaller>();
        }

        void InstallInquiry()
        {
            Container.BindInterfacesTo<InquiryDialogPresenter>().AsCached();
            Container.BindViewFactoryInfo<InquiryDialogViewController, InquiryDialogViewControllerInstaller>();
            Container.Bind<GetInquiryModelUseCase>().AsCached();
        }

        void InstallAnnouncement()
        {
            Container.BindInterfacesTo<AnnouncementViewFacade>().AsCached();
            Container.BindViewFactoryInfo<AnnouncementMainViewController, AnnouncementMainViewControllerInstaller>();
            Container.BindInterfacesTo<AnnouncementService>().AsCached();
            Container.Bind<CheckAllAnnouncementReadUseCase>().AsCached();
        }

        void InstallTitle()
        {
            Container.Bind<LoginUseCases>().AsCached();
            Container.BindInterfacesTo<SelectStageInteractor>().AsCached();
            Container.BindInterfacesTo<PvpStartModelFactory>().AsCached();
            Container.Bind<InitializeIAPUseCase>().AsCached();

            Container.BindViewFactoryInfo<
                AppTrackingTransparencyConfirmViewController,
                AppTrackingTransparencyConfirmViewControllerInstaller>();
            Container.BindInterfacesTo<MstDataService>().AsCached();
            Container.BindViewFactoryInfo<AssetDownloadNoticeViewController, AssetDownloadNoticeViewControllerInstaller>();
            Container.BindViewFactoryInfo<UserNameEditDialogViewController, TutorialUserNameEditDialogViewControllerInstaller>();
        }

        void InstallSessionResume()
        {
            Container.Bind<SessionAbortUseCase>().AsCached();
            Container.Bind<PvpSessionResumeUseCase>().AsCached();
            Container.Bind<StageSessionResumeUseCase>().AsCached();
            Container.Bind<SessionResumeStateRegistrationUseCase>().AsCached();
        }

        void InstallAgreement()
        {
            Container.BindViewFactoryInfo<TermsOfServiceViewController, TermsOfServiceViewControllerInstaller>();
            Container.BindViewFactoryInfo<AgreementDialogViewController, AgreementDialogViewControllerInstaller>();
        }

        void InstallPass()
        {
            Container.Bind<InitializePassEffectUseCase>().AsCached();
        }
    }
}
