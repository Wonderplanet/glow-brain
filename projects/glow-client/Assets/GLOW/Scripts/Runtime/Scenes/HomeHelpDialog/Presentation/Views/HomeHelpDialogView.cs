using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views
{
    public class HomeHelpDialogView : UIView
    {
        [SerializeField] HomeHelpFoldingListComponent _foldingListComponent;

        public void SetUp(HomeHelpViewModel viewModel)
        {
            _foldingListComponent.SetUp(viewModel.MainContents, SetInteractable);
        }

        void SetInteractable(bool interactable)
        {
            this.Interactable = interactable;
        }
    }
}
