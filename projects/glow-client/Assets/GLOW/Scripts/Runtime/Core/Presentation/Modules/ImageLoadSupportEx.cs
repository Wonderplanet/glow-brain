using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;
using Zenject;

namespace GLOW.Core.Presentation.Modules
{
    public interface ISpriteLoadControlEx
    {
    }

    public interface IBannerLoadSupport
    {
        BannerLoaderEx LoadBanner(RawImage image, string assetPath, Action completion = null);
        UniTask LoadBanner(CancellationToken cancellationToken, RawImage image, string assetPath);
    }
    

    public class BannerLoadSupportEx : IBannerLoadSupport
    {
        IBannerSource bannerSource = null;
        LoadingIndicatorPool loadingIndicatorPool = null;

        [InjectOptional] INoImageComponentProvider NoImageComponentProvider { get; }
        [Inject]
        public void Inject(IBannerSource bannerSource, [InjectOptional] LoadingIndicatorPool loadingIndicatorPool)
        {
            this.bannerSource = bannerSource;
            this.loadingIndicatorPool = loadingIndicatorPool;
        }

        public BannerLoaderEx AddTo(RawImage image)
        {
            var loader = image.GetComponent<BannerLoaderEx>();
            if (loader == null)
            {
                loader = image.gameObject.AddComponent<BannerLoaderEx>();
                loader.Inject(bannerSource, NoImageComponentProvider);
                loader.LoadingIndicatorPool = loadingIndicatorPool;
            }
            return loader;
        }

        BannerLoaderEx IBannerLoadSupport.LoadBanner(RawImage image, string assetPath, Action completion)
        {
            var loader = AddTo(image);
            loader.Load(assetPath, completion);
            return loader;
        }

        async UniTask IBannerLoadSupport.LoadBanner(CancellationToken cancellationToken, RawImage image, string assetPath)
        {
            var loader = AddTo(image);
            await loader.Load(cancellationToken, assetPath);
        }
    }
}