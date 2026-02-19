using GLOW.Core.Presentation.Components;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views
{
    public class OutpostArtworkChangeConfirmView : UIView
    {
        [SerializeField] UIImage _beforeArtworkImage;
        [SerializeField] UIImage _afterArtworkSImage;

        public void Setup(OutpostArtworkChangeConfirmViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_beforeArtworkImage.Image, viewModel.BeforeArtworkSmallPath.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_afterArtworkSImage.Image, viewModel.AfterArtworkSmallPath.Value);
        }
    }
}
