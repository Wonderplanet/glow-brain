using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.Notice.Presentation.Component
{
    public class NoticeBannerComponent : UIObject
    {
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] CanvasGroup _bannerCanvasGroup;
        [SerializeField] UIImage _backgroundImage;
        [SerializeField] bool _setNativeSizeExecute;

        public void SetupDownloadBanner(NoticeBannerUrl bannerUrl)
        {
            _backgroundImage.Hidden = false;
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, bannerUrl.Value, () =>
            {
                if (_setNativeSizeExecute)
                {
                    _bannerRawImage.SetNativeSize();
                }

                _bannerCanvasGroup
                    .DOFade(0.0f, 0.15f)
                    .SetEase(Ease.OutQuad);
            });
        }
    }
}