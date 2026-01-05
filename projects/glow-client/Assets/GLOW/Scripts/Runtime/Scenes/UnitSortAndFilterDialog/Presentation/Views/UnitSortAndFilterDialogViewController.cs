using System;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Constants;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Views
{
    public class UnitSortAndFilterDialogViewController : UIViewController<UnitSortAndFilterDialogView>
    {
        public record Argument(UnitSortAndFilterDialogViewModel ViewModel, Action OnCancel, Action OnConfirm);

        [Inject] IUnitSortAndFilterDialogViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(Args.ViewModel);
        }

        public void InitializeSort(UnitListSortType currentSortType, Action<UnitListSortType> onToggleChange)
        {
            ActualView.InitializeSort(currentSortType, onToggleChange);
        }

        public void InitializeFilter(UnitSortAndFilterDialogViewModel viewModel)
        {
            ActualView.InitializeFilter(viewModel);
        }

        public void SetTab(UnitSortAndFilterTabType tabType, UnitListSortType currentSortType)
        {
            ActualView.SetTab(tabType, currentSortType);
        }

        public void SetSortToggle(UnitListSortType setSortType)
        {
            ActualView.SetSortToggle(setSortType);
        }
        
        public void SetEventBonusSortItemHidden(FilterBonusFlag bonusFlag)
        {
            ActualView.SetEventBonusSortItemHidden(bonusFlag);
        }

        [UIAction]
        void OnConfirm()
        {
            ViewDelegate.OnConfirm();
        }

        [UIAction]
        void OnCancel()
        {
            ViewDelegate.OnCancel();
        }

        [UIAction]
        void OnSortTabClicked()
        {
            ViewDelegate.OnSortAndFilterTabClicked(UnitSortAndFilterTabType.Sort);
        }

        [UIAction]
        void OnFilterTabClicked()
        {
            ViewDelegate.OnSortAndFilterTabClicked(UnitSortAndFilterTabType.Filter);
        }
    }
}
