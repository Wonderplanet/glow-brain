using System;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Core.Presentation.Modules
{
    public class UIBannerLoaderEx
    {
        [Inject] IBannerLoadSupport BannerLoadSupport { get; }
        public static UIBannerLoaderEx Main { get; private set; }

        public UIBannerLoaderEx()
        {
            Main = this;
        }

        public void LoadBannerWithFadeIfNotLoaded(RawImage image, string assetPath, Action completion = null)
        {
            BannerLoadSupport.LoadBanner(image, assetPath, completion);
        }
    }
}