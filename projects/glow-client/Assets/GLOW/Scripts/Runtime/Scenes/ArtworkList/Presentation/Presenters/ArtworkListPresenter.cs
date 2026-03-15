using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ArtworkList.Domain.Models;
using GLOW.Scenes.ArtworkList.Domain.UseCases;
using GLOW.Scenes.ArtworkList.Presentation.Translators;
using GLOW.Scenes.ArtworkList.Presentation.Views;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Translator;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkList.Presentation.Presenters
{
    public class ArtworkListPresenter : IArtworkListViewDelegate
    {
        [Inject] ArtworkListViewController ViewController { get; }
        [Inject] GetArtworkListUseCase GetArtworkListUseCase { get; }
        [Inject] GetArtworkSortAndFilterUseCase GetArtworkSortAndFilterUseCase { get; }
        [Inject] UpdateArtworkSortOrderUseCase UpdateArtworkSortOrderUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }

        ArtworkListUseCaseModel _useCaseModel;


        void IArtworkListViewDelegate.OnViewWillAppear()
        {
            UpdateArtworkList();
        }

        void IArtworkListViewDelegate.OnListCellTapped(MasterDataId mstArtworkId)
        {
            var argument = new ArtworkEnhanceViewController.Argument(
                mstArtworkId,
                _useCaseModel.ArtworkList.Select(model => model.MstArtworkId).ToList());
            var viewController = ViewFactory.Create<
                ArtworkEnhanceViewController,
                ArtworkEnhanceViewController.Argument>(argument);

            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IArtworkListViewDelegate.OnSortAndFilterButtonTapped()
        {
            var useCaseModel =
                GetArtworkSortAndFilterUseCase.GetArtworkSortAndFilterDialogModel(
                    ArtworkSortFilterCacheType.ArtworkList);
            var viewModel = ArtworkSortAndFilterDialogViewModelTranslator.Translate(useCaseModel);

            var argument = new ArtworkSortAndFilterDialogViewController.Argument(
                viewModel,
                null,
                UpdateArtworkList);
            var dialogViewController = ViewFactory.Create<
                ArtworkSortAndFilterDialogViewController,
                ArtworkSortAndFilterDialogViewController.Argument>(argument);
            ViewController.PresentModally(dialogViewController);
        }

        void IArtworkListViewDelegate.OnSortButtonTapped()
        {
            var switchSortOrder = _useCaseModel.SortFilterCategoryModel.SortOrder == ArtworkListSortOrder.Ascending
                ? ArtworkListSortOrder.Descending
                : ArtworkListSortOrder.Ascending;

            UpdateArtworkSortOrderUseCase.UpdateSortOrder(switchSortOrder, ArtworkSortFilterCacheType.ArtworkList);
            UpdateArtworkList();
        }

        void UpdateArtworkList()
        {
            _useCaseModel = GetArtworkListUseCase.GetArtworkListUseCaseModel();
            var viewModel = ArtworkListViewModelTranslator.TranslateToFormationListViewModel(_useCaseModel);

            ViewController.SetUp(viewModel);
        }
    }
}

