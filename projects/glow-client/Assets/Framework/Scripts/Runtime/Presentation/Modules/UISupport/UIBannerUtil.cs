using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public sealed class UIBannerUtil
    {
        [Inject] IBannerLoadSupport BannerLoadSupport { get; }

        static UIBannerUtil Main { get; set; }

        public UIBannerUtil()
        {
            Main = this;
        }

        public static void LoadWithFadeIfNotLoaded(RawImage image, string assetPath, Action completion = null)
        {
            Main.BannerLoadSupport.LoadBanner(image, assetPath, completion);
        }

        public static async UniTask Load(CancellationToken cancellationToken, RawImage image, string assetPath)
        {
            await Main.BannerLoadSupport.LoadBanner(cancellationToken, image, assetPath);
        }
    }
}
