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
    public class RandomFragmentBoxViewController : UIViewController<RandomFragmentBoxView>, IEscapeResponder
    {
        public record Argument(
            ItemModel RandomFragmentBoxItemModel,
            ItemAmount LimitUseAmount,
            Action OnUserItemUpdated,
            Action OnTryReshowView);

        [Inject] IRandomFragmentBoxViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this,ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(RandomFragmentBoxViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void PlayShowAnimation()
        {
            ActualView.PlayShowAnimation();
        }

        public void PlayCloseAnimation()
        {
            ActualView.PlayCloseAnimation();
        }

        [UIAction]
        public void OnCancelButtonTapped()
        {
            ViewDelegate.OnCancelSelected();
        }

        [UIAction]
        public void OnUseButtonTapped()
        {
            var amount = ActualView.SelectedItemAmount;
            ViewDelegate.OnUseSelected(amount);
        }

        [UIAction]
        public void OnLineupButtonTapped()
        {
            ViewDelegate.OnLineupSelected();
        }
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnCancelSelected();
            return true;
        }
    }
}
