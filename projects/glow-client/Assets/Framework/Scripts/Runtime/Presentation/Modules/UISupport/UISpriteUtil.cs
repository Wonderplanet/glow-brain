using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public sealed class UISpriteUtil
    {
        [Inject] ISpriteLoadSupport SpriteLoadSupport { get; }

        static UISpriteUtil Main { get; set; }

        public UISpriteUtil()
        {
            Main = this;
        }

        public static void LoadSpriteWithFadeIfNotLoaded(Image image, string assetPath, Action completion = null)
        {
            Main.SpriteLoadSupport.LoadSprite(image, assetPath, ImageTransitionStrategy.DisableAndFadeIfNotLoaded, completion);
        }

        public static void LoadSpriteWithFade(Image image, string assetPath, Action completion = null)
        {
            Main.SpriteLoadSupport.LoadSprite(image, assetPath, ImageTransitionStrategy.DisableAndFade, completion);
        }

        public static async UniTask Load(CancellationToken cancellationToken, Image image, string assetPath)
        {
            await Main.SpriteLoadSupport.LoadSprite(cancellationToken, image, assetPath);
        }
    }
}
