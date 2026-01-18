using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views
{
    /// <summary>
    /// 91_図鑑
    /// 　91-5_作品別エンブレム表示
    /// 　　91-5-1エンブレム詳細ダイアログ
    /// </summary>
    public class EncyclopediaEmblemDetailView : UIView
    {
        [SerializeField] UIImage _emblemImage;
        [SerializeField] UIText _emblemName;
        [SerializeField] UIText _emblemDescription;

        public void Setup(EncyclopediaEmblemDetailViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFade(_emblemImage.Image, viewModel.IconAssetPath.Value);
            _emblemName.SetText(viewModel.Name.Value);
            _emblemDescription.SetText(viewModel.Description.Value);
        }
    }
}
