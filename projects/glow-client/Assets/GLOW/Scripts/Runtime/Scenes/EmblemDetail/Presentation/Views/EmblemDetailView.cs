using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EmblemDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EmblemDetail.Presentation.Views
{
    /// <summary>
    /// エンブレム詳細ダイアログ
    /// </summary>
    public class EmblemDetailView : UIView
    {
        [SerializeField] UIImage _emblemImage;
        [SerializeField] UIText _emblemName;
        [SerializeField] UIText _emblemDescription;

        public void SetUp(EmblemDetailViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFade(_emblemImage.Image, viewModel.IconAssetPath.Value);
            _emblemName.SetText(viewModel.Name.Value);
            _emblemDescription.SetText(viewModel.Description.Value);
        }
    }
}
