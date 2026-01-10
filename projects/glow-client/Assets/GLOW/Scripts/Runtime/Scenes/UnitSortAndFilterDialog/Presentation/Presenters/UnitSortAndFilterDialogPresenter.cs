using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.UseCases;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Presenters
{
    public class UnitSortAndFilterDialogPresenter : IUnitSortAndFilterDialogViewDelegate
    {
        [Inject] UnitSortAndFilterDialogViewController ViewController { get; }
        [Inject] UnitSortAndFilterDialogViewController.Argument Argument { get; }
        [Inject] UpdateSortFilterCacheUseCase UpdateSortFilterCacheUseCase { get; }
        [Inject] HasAnyMatchingFilterUnitUseCase HasAnyMatchingFilterUnitUseCase { get; }

        UnitSortAndFilterDialogViewModel _viewModel;
        UnitListSortType _currentSortType;

        void IUnitSortAndFilterDialogViewDelegate.OnViewDidLoad(UnitSortAndFilterDialogViewModel viewModel)
        {
            _viewModel = viewModel;
            _currentSortType = _viewModel.CategoryModel.SortType;

            var setTabType = viewModel.CategoryModel.IsAnyFilter()
                ? UnitSortAndFilterTabType.Filter
                : UnitSortAndFilterTabType.Sort;
            ViewController.SetTab(setTabType, _currentSortType);
            ViewController.InitializeSort(_viewModel.CategoryModel.SortType, OnSortToggleChange);
            ViewController.InitializeFilter(_viewModel);
            ViewController.SetEventBonusSortItemHidden(_viewModel.CategoryModel.FilterBonusModel.EnableBonus);
        }

        void IUnitSortAndFilterDialogViewDelegate.OnConfirm()
        {
            // フィルター・ソート設定保存対応
            var cacheType = _viewModel.UnitSortFilterCacheType;

            // フィルター設定に該当するユニットがいなければトーストを出した上で確定せずにダイアログを表示したままにする
            if (!HasAnyMatchingFilterUnitUseCase.HasAnyMatchingFilterUnit(
                    ViewController.ActualView.RarityOnToggleTypes,
                    ViewController.ActualView.ColorOnToggleTypes,
                    ViewController.ActualView.RoleOnToggleTypes,
                    ViewController.ActualView.RangeOnToggleTypes,
                    ViewController.ActualView.SeriesOnToggleMasterDataIds,
                    ViewController.ActualView.SpecialAttackOnToggleTypes,
                    ViewController.ActualView.AbilityOnToggleTypes,
                    _viewModel.CategoryModel.FilterBonusModel.EnableBonus,
                    ViewController.ActualView.BonusOnToggle,
                    _viewModel.CategoryModel.FilterFormationModel.EnableFormationFlag,
                    ViewController.ActualView.AchievedOnToggle,
                    ViewController.ActualView.NotAchieveOnToggle,
                    _currentSortType,
                    _viewModel.CategoryModel.SortOrder,
                    _viewModel.SpecialRuleTargetMstStageId,
                    _viewModel.SpecialRuleContentType))
            {
                CommonToastWireFrame.ShowScreenCenterToast("条件に当てはまるキャラがいません");
                return;
            }

            UpdateSortFilterCacheUseCase.UpdateModel(
                cacheType,
                ViewController.ActualView.RarityOnToggleTypes,
                ViewController.ActualView.ColorOnToggleTypes,
                ViewController.ActualView.RoleOnToggleTypes,
                ViewController.ActualView.RangeOnToggleTypes,
                ViewController.ActualView.SeriesOnToggleMasterDataIds,
                ViewController.ActualView.SpecialAttackOnToggleTypes,
                ViewController.ActualView.AbilityOnToggleTypes,
                _viewModel.CategoryModel.FilterBonusModel.EnableBonus,
                ViewController.ActualView.BonusOnToggle,
                _viewModel.CategoryModel.FilterFormationModel.EnableFormationFlag,
                ViewController.ActualView.AchievedOnToggle,
                ViewController.ActualView.NotAchieveOnToggle,
                _currentSortType,
                _viewModel.CategoryModel.SortOrder);

            Argument.OnConfirm?.Invoke();
            ViewController.Dismiss();
        }

        void IUnitSortAndFilterDialogViewDelegate.OnCancel()
        {
            Argument.OnCancel?.Invoke();
            ViewController.Dismiss();
        }

        void IUnitSortAndFilterDialogViewDelegate.OnSortAndFilterTabClicked(UnitSortAndFilterTabType tabType)
        {
            ViewController.SetTab(tabType, _currentSortType);
        }

        /// <summary> 選択ソート変更時 </summary>
        void OnSortToggleChange(UnitListSortType setSortType)
        {
            ViewController.SetSortToggle(setSortType);
            _currentSortType = setSortType;
        }
    }
}
