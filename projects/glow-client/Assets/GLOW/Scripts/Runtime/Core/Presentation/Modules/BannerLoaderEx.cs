using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.ResourceManagement;

namespace GLOW.Core.Presentation.Modules
{
    /// <summary>
    /// WonderPlanet.ResourceManagementのBannerLoaderをGLOW用に調整したもの
    /// </summary>
    [RequireComponent(typeof(RawImage))]
    public class BannerLoaderEx : MonoBehaviour
    {
        [SerializeField] NoImageComponent noImageComponentPrefab;
        public IBannerSource BannerSource { get; private set; }

        RawImage image = null;
        IAssetReference<Texture2D> retainedBanner = null;
        INoImageComponentProvider NoImageComponentProvider { get; set; }
        string loadedAssetPath = null;
        CancellationTokenSource clearedCancellationTokenSource = new CancellationTokenSource();

        public LoadingIndicatorPool LoadingIndicatorPool { get; set; }
        GameObject loadingIndicator = null;
        NoImageComponent noImageComponent;

        RawImage Image
        {
            get
            {
                if (image != null)
                {
                    return image;
                }

                image = GetComponent<RawImage>();
                image.texture = null;
                return image;
            }
        }

        void Awake()
        {
            Image.texture = null;
            Image.enabled = false;
        }

        public void Inject(IBannerSource bannerSource, INoImageComponentProvider noImageComponentProvider)
        {
            BannerSource = bannerSource;
            NoImageComponentProvider = noImageComponentProvider;
        }

        public void Clear()
        {
            loadedAssetPath = null;

            clearedCancellationTokenSource?.Cancel();
            clearedCancellationTokenSource?.Dispose();
            clearedCancellationTokenSource = new CancellationTokenSource();

            Image.texture = null;
            Image.enabled = false;
            retainedBanner?.Release();
        }

        public void Load(string assetPath, Action completion = null)
        {
            // NOTE: 完了時にイベントを知りたい時に利用
            //       UniTask.Createは同一スレッドで即時実行される
            UniTask.Create(async () =>
                {
                    await Load(default, assetPath);
                    completion?.Invoke();
                })
                .Forget();
        }

        public async UniTask Load(CancellationToken cancellationToken, string assetPath)
        {
            if (loadedAssetPath == assetPath)
            {
                return;
            }

            loadedAssetPath = assetPath;

            Image.enabled = false;

            if (LoadingIndicatorPool != null && !BannerSource.IsInMemoryCached(assetPath))
            {
                loadingIndicator = LoadingIndicatorPool.DequeueReusableIndicator();
                loadingIndicator.transform.SetParent(this.transform, false);
            }

            try
            {
                using var cancellationTokenSource =
                    CancellationTokenSource.CreateLinkedTokenSource(clearedCancellationTokenSource.Token, this.GetCancellationTokenOnDestroy(), cancellationToken);

                var assetReference = await BannerSource.GetBanner(cancellationTokenSource.Token, assetPath);
                // NOTE: 同じデータを取得した場合は即時完了を通知する
                if (retainedBanner == assetReference)
                {
                    return;
                }
                
                // NoImageが既に表示されていて、読み込みが成功した場合NoImagComponentを非表示にする
                if (noImageComponent != null)
                {
                    noImageComponent.HideNoImage();
                }

                retainedBanner?.Release();

                assetReference.Retain();
                retainedBanner = assetReference;
                Image.texture = assetReference.Value;
                Image.enabled = true;
            }
            catch (Exception e)
            {
                // エラーにはしない
                Debug.LogWarning("Banner Load Failed. " + assetPath + " : " + e.Message);
                if (null == this) return;
                Image.texture = null;
                // NoImageを生成済みの場合再表示する
                if (noImageComponent != null)
                {
                    noImageComponent.ShowNoImage();
                }
                else if (noImageComponentPrefab != null)
                {
                    // SerializeFieldで設定されている場合はそれを使う
                    noImageComponent = Instantiate(noImageComponentPrefab, Image.transform);
                }
                else
                {
                    // SerializeFieldで設定されていない場合はProviderから取得する
                    if(NoImageComponentProvider != null)
                    {
                        noImageComponent = Instantiate(NoImageComponentProvider.ProvideNoImageComponent(), Image.transform);
                    }
                }
                
                // NoImage読み込みができた場合、画像を非表示にする
                Image.enabled = (noImageComponent == null);
                
                // デバッグビルドでのみパスを表示
                if (Debug.isDebugBuild && noImageComponent != null)
                {
                    noImageComponent.SetNoImagePathText(assetPath);
                }
            }
            finally
            {
                EnqueueIndicatorIfNeed();
            }
        }

        void EnqueueIndicatorIfNeed()
        {
            if (LoadingIndicatorPool != null && loadingIndicator != null)
            {
                LoadingIndicatorPool.EnqueueReusableIndicator(loadingIndicator);
            }
        }

        void OnDestroy()
        {
            EnqueueIndicatorIfNeed();
            clearedCancellationTokenSource?.Cancel();
            clearedCancellationTokenSource?.Dispose();
            retainedBanner?.Release();
        }
    }
}