using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Presenters
{
    public class ArtworkSortAndFilterDialogPresenter : IArtworkSortAndFilterDialogViewDelegate
    {
        [Inject] ArtworkSortAndFilterDialogViewController ViewController { get; }
        [Inject] ArtworkSortAndFilterDialogViewController.Argument Argument { get; }
        [Inject] HasAnyMatchingFilterArtworkUseCase HasAnyMatchingFilterArtworkUseCase { get; }
        [Inject] UpdateArtworkSortFilterCacheUseCase UpdateArtworkSortFilterCacheUseCase { get; }

        ArtworkSortAndFilterDialogViewModel _viewModel;
        ArtworkListSortType _currentSortType;

        void IArtworkSortAndFilterDialogViewDelegate.OnViewDidLoad(ArtworkSortAndFilterDialogViewModel viewModel)
        {
            _viewModel = viewModel;
            _currentSortType = _viewModel.CategoryModel.SortType;

            ViewController.InitializeSort(_viewModel.CategoryModel.SortType, OnSortToggleChange);
            ViewController.InitializeFilter(_viewModel);
        }

        void IArtworkSortAndFilterDialogViewDelegate.OnConfirm()
        {
            // フィルター・ソート設定保存対応
            var cacheType = _viewModel.CacheType;

            if (!HasAnyMatchingFilterArtworkUseCase.HasAnyMatchingFilterUnit(
                    ViewController.ActualView.SeriesOnToggleMasterDataIds,
                    ViewController.ActualView.ArtworkEffectOnToggleTypes,
                    _currentSortType,
                    _viewModel.CategoryModel.SortOrder))
            {
                CommonToastWireFrame.ShowScreenCenterToast("条件に当てはまるキャラがいません");
                return;
            }

            UpdateArtworkSortFilterCacheUseCase.UpdateArtworkSortFilterCache(
                cacheType,
                ViewController.ActualView.SeriesOnToggleMasterDataIds,
                ViewController.ActualView.ArtworkEffectOnToggleTypes,
                _currentSortType,
                _viewModel.CategoryModel.SortOrder);

            Argument.OnConfirm?.Invoke();
            ViewController.Dismiss();
        }

        void IArtworkSortAndFilterDialogViewDelegate.OnCancel()
        {
            Argument.OnCancel?.Invoke();
            ViewController.Dismiss();
        }

        void OnSortToggleChange(ArtworkListSortType setSortType)
        {
            ViewController.SetSortToggle(setSortType);
            _currentSortType = setSortType;
        }
    }
}
