using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Presentation.Translator;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter
{
    public class ArtworkAcquisitionRoutePresenter : IArtworkAcquisitionRouteDelegate
    {
        [Inject] ArtworkAcquisitionRouteViewController.Argument Argument { get; }
        [Inject] ArtworkAcquisitionRouteViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] SetArtworkFragmentDropQuestUseCase SetArtworkFragmentDropQuestUseCase { get; }
        [Inject] ApplyUpdatedOutpostArtworkUseCase ApplyUpdatedOutpostArtworkUseCase { get; }
        [Inject] ArtworkAcquisitionRouteUseCase ArtworkAcquisitionRouteUseCase { get; }

        void IArtworkAcquisitionRouteDelegate.OnViewDidLoad()
        {
            ViewController.InitUICollectionView();

            var useCaseModel = ArtworkAcquisitionRouteUseCase.CreateArtworkSourceModel(Argument.MstArtworkId);
            var viewModel = ArtworkAcquisitionRouteTranslator.Translate(useCaseModel);
            ViewController.SetUpView(viewModel);
        }

        void IArtworkAcquisitionRouteDelegate.OnSelectFragmentDropQuest(EncyclopediaArtworkFragmentListCellViewModel viewModel)
        {
            if (viewModel.StatusFlags.IsOutOfTermQuest)
            {
                CommonToastWireFrame.ShowScreenCenterToast("開催していないクエストです\n次回開催をお待ちください");
                return;
            }

            if (!viewModel.StatusFlags.IsEnableChallenge)
            {
                CommonToastWireFrame.ShowScreenCenterToast("ステージに挑戦できません");
                return;
            }

            if (QuestType.Normal == viewModel.DropQuestType)
            {
                ViewController.CloseView();
                ViewController.OnTransitionAction?.Invoke();
                SetArtworkFragmentDropQuestUseCase.SetSelectedStage(viewModel.MstArtworkFragmentId);
                HomeViewNavigation.TryPopToRoot(completion: () => HomeViewControl.OnQuestSelected());
                return;
            }

            if (QuestType.Event == viewModel.DropQuestType)
            {
                ViewController.CloseView();
                ViewController.OnTransitionAction?.Invoke();
                SetArtworkFragmentDropQuestUseCase.SetSelectedStage(viewModel.MstArtworkFragmentId);
                HomeViewControl.OnContentTopSelected();
                return;
            }
        }

        void IArtworkAcquisitionRouteDelegate.OnBackButtonTapped()
        {
            ViewController.CloseView();
        }
    }
}
