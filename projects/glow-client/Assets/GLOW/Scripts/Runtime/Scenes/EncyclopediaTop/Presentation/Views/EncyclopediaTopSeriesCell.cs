using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EncyclopediaTop.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EncyclopediaTop.Presentation.Views
{
    public class EncyclopediaTopSeriesCell : UICollectionViewCell
    {
        [SerializeField] UIImage _image;
        [SerializeField] UIText _seriesName;
        [SerializeField] UIText _unlockCountText;
        [SerializeField] UIText _maxCountText;
        [SerializeField] UIObject _badge;

        public void Setup(EncyclopediaTopSeriesCellViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image.Image, viewModel.ImagePath.Value);
            _seriesName.SetText(viewModel.Name.Value);
            _unlockCountText.SetText(viewModel.UnlockCount.ToString());
            _maxCountText.SetText("/{0}", viewModel.MaxCount);
            _badge.Hidden = !viewModel.Badge;
        }
    }
}
