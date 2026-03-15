using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Translator;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter
{
    public class ArtworkEnhancePresenter : IArtworkEnhanceDelegate
    {
        [Inject] ArtworkEnhanceViewController ViewController { get; }
        [Inject] ArtworkEnhanceViewController.Argument Argument { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ArtworkEnhanceUseCase ArtworkEnhanceUseCase { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] InitializeEncyclopediaArtworkCacheUseCase InitializeEncyclopediaArtworkCacheUseCase { get; }

        void IArtworkEnhanceDelegate.OnViewDidLoad()
        {
            SetUpView(Argument.MstFirstArtworkId);
            SetUpArtworkPageComponent();
        }

        void SetUpView(MasterDataId mstArtworkId)
        {
            var useCaseModel = ArtworkEnhanceUseCase.CreateArtworkEnhanceUseCaseModel(mstArtworkId);
            var viewModel = ArtworkEnhanceViewModelTranslator.Translate(useCaseModel);
            ViewController.SetUpView(viewModel);
        }

        void SetUpArtworkPageComponent()
        {
            ViewController.SetUpArtworkPageComponent(
                ViewFactory,
                Argument.MstFirstArtworkId,
                Argument.MstArtworkIds);
        }

        void IArtworkEnhanceDelegate.OnSwitchArtwork(MasterDataId mstArtworkId)
        {
            SetUpView(mstArtworkId);
        }

        void IArtworkEnhanceDelegate.OnEnhanceButtonTapped(MasterDataId mstArtworkId)
        {
            var argument = new ArtworkGradeUpConfirmViewController.Argument(
                mstArtworkId,
                () =>
                {
                    SetUpView(mstArtworkId);
                    ViewController.UpdateCurrentPageView();
                });
            var controller = ViewFactory.Create<ArtworkGradeUpConfirmViewController,
                ArtworkGradeUpConfirmViewController.Argument>(argument);

            ViewController.PresentModally(controller);
        }

        void IArtworkEnhanceDelegate.OnItemIconTapped(PlayerResourceIconViewModel viewModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(viewModel, ViewController);
        }

        void IArtworkEnhanceDelegate.OnInfoButtonTapped(MasterDataId mstArtworkId)
        {
            var argument = new ArtworkAcquisitionRouteViewController.Argument(mstArtworkId);
            var controller = ViewFactory.Create<ArtworkAcquisitionRouteViewController,
                ArtworkAcquisitionRouteViewController.Argument>(argument);

            ViewController.PresentModally(controller);
        }

        void IArtworkEnhanceDelegate.OnBackButtonTapped()
        {
            ViewController.OnClose();
            HomeViewNavigation.TryPop();
        }
    }
}
