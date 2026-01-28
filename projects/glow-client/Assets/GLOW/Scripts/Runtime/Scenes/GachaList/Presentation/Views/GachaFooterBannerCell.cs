using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class GachaFooterBannerCell : GlowCustomInfiniteCarouselCell
    {
        [SerializeField] UIImage _image;
        public void Setup(GachaFooterBannerViewModel model)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_image.Image, model.GachaBannerAssetPath.Value);
        }
    }
}
