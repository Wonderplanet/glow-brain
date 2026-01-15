using GLOW.Modules.Tutorial.Application.Context;
using GLOW.Modules.Tutorial.Domain.Applier;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Presentation.Presenters;
using GLOW.Modules.Tutorial.Presentation.Sequence;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using GLOW.Modules.TutorialTipDialog.Domain.UseCase;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.AssetDownloadNotice.Application.Views;
using GLOW.Scenes.AssetDownloadNotice.Presentation.Views;
using GLOW.Scenes.GachaList.Domain.Applier;
using GLOW.Scenes.InGame.Domain.UseCases;
using WPFramework.Application.Modules;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Installers
{
    public class InGameTutorialInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<InGameTutorialContext>().AsCached();
            Container.BindInterfacesTo<IntroductionTutorialContext>().AsCached();
            Container.BindInterfacesTo<TutorialStatusApplier>().AsCached();
            Container.BindInterfacesTo<InGameResultFreePartTutorialContext>().AsCached();
            Container.BindInterfacesTo<UserTutorialFreePartModelsApplier>().AsCached();
            Container.BindInterfacesTo<InGameTutorialBackKeyHandler>().AsCached();
            Container.BindFactory<IntroductionMangaSequence, 
                PlaceholderFactory<IntroductionMangaSequence>>().AsCached();
            Container.BindFactory<InGameIntroductionTutorialSequence, 
                PlaceholderFactory<InGameIntroductionTutorialSequence>>().AsCached();
            Container.BindFactory<InGame1TutorialSequence,
                PlaceholderFactory<InGame1TutorialSequence>>().AsCached();
            Container.BindFactory<InGame2TutorialSequence, 
                PlaceholderFactory<InGame2TutorialSequence>>().AsCached();
            Container.BindFactory<ArtworkFragmentTutorialSequence, 
                PlaceholderFactory<ArtworkFragmentTutorialSequence>>().AsCached();

            Container.Bind<TutorialTipDialogUseCase>().AsCached();
            Container.Bind<TutorialTipDialogViewWireFrame>().AsCached();
            Container.Bind<CompleteFreePartTutorialUseCase>().AsCached();
            Container.Bind<CheckTutorialCompletedUseCase>().AsCached();
            Container.Bind<ProgressTutorialStatusUseCase>().AsCached();
            Container.Bind<TutorialTransitionSkipUseCase>().AsCached();
            Container.Bind<TutorialChargeRushGaugeUseCase>().AsCached();
            Container.Bind<TutorialChangeSummonCostToZeroUseCase>().AsCached();
            Container.Bind<TutorialChangeFirstUnitSummonCostToZero>().AsCached();
            Container.Bind<TutorialChangeFirstUnitRemainingSpecialAttackCoolTimeToZeroUseCase>().AsCached();
            Container.Bind<IntroductionTutorialSkipUseCase>().AsCached();
            Container.Bind<SkipIntroductionTutorialUseCase>().AsCached();
            Container.Bind<TutorialBackGroundDownloadUseCase>().AsCached();
            Container.BindViewFactoryInfo<AssetDownloadNoticeViewController,
                AssetDownloadNoticeViewControllerInstaller>();
        }
    }
}
