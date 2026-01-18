using System;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public class SelectionFragmentBoxViewController : UIViewController<SelectionFragmentBoxView>, IEscapeResponder
    {
        public record Argument(ItemModel ItemModel, ItemAmount LimitUseAmount,
            Action OnUserItemUpdated, Action OnTryReshowView,
            MasterDataId SelectedMstItemId);

        [Inject] ISelectionFragmentBoxViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(SelectionFragmentBoxViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }
        
        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
                return false;

            ViewDelegate.OnCancelSelected();
            return true;
        }

        [UIAction]
        public void OnCancelButtonTapped()
        {
            ViewDelegate.OnCancelSelected();
        }

        [UIAction]
        public void OnUseButtonTapped()
        {
            var selectedItemId = ActualView.SelectedItemId;
            var amount = ActualView.SelectedItemAmount;

            ViewDelegate.OnUseSelected(selectedItemId, amount);
        }

        [UIAction]
        void OnTapInfoButton()
        {
            ViewDelegate.OnTapInfoButton();
        }

    }
}
