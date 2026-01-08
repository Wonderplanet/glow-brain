using System.Collections.Generic;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public sealed class ItemBoxViewController : HomeBaseViewController<ItemBoxView>
    {
        [Inject] IItemBoxViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnItemIconTapped = ViewDelegate.OnItemSelected;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.ViewDidUnload();
        }

        public void InitializeItemList()
        {
            ActualView.InitializeItemList();
        }

        public void SetupItemListAndReload(ItemBoxTabType itemBoxTabType, ItemBoxIconListViewModel viewModel)
        {
            ActualView.SetupItemListAndReload(itemBoxTabType, viewModel);
        }

        public void PlayCellAppearanceAnimation(ItemBoxTabType itemBoxTabType)
        {
            ActualView.PlayCellAppearanceAnimation(itemBoxTabType);
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackSelected();
        }

        [UIAction]
        public void OnItemGroupButtonTapped()
        {
            ViewDelegate.OnItemGroupSelected(ItemBoxTabType.Item);
        }

        [UIAction]
        public void OnEnhanceItemGroupButtonTapped()
        {
            ViewDelegate.OnItemGroupSelected(ItemBoxTabType.Enhance);
        }

        [UIAction]
        public void OnFragmentGroupButtonTapped()
        {
            ViewDelegate.OnItemGroupSelected(ItemBoxTabType.CharacterFragment);
        }
    }
}
