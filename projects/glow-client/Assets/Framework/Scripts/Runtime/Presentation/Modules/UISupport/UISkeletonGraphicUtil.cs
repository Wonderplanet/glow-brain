using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using Spine.Unity;
using WonderPlanet.ResourceManagement.Spine;
using Zenject;

namespace WPFramework.Presentation.Modules
{
    public sealed class UISkeletonGraphicUtil
    {
        [Inject] ISkeletonGraphicLoadSupport SkeletonGraphicLoadSupport { get; }

        static UISkeletonGraphicUtil Main { get; set; }

        public UISkeletonGraphicUtil()
        {
            Main = this;
        }

        public static void LoadWithFadeIfNotLoaded(SkeletonGraphic skeletonGraphic, string assetPath, bool useRenderTexture = false, Action completion = null)
        {
            Main.SkeletonGraphicLoadSupport.LoadSkeletonWithFadeIfNotLoaded(skeletonGraphic, assetPath, useRenderTexture, completion);
        }

        public static async UniTask Load(CancellationToken cancellationToken, SkeletonGraphic skeletonGraphic, string assetPath)
        {
            await Main.SkeletonGraphicLoadSupport.LoadSkeleton(cancellationToken, skeletonGraphic, assetPath);
        }
    }
}
