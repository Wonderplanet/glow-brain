using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainBanner
{
    public class HomeMainBannerItemView : UIView
    {
        [SerializeField] RawImage _bannerImage;
        public RawImage BannerImage => _bannerImage;

    }
}
