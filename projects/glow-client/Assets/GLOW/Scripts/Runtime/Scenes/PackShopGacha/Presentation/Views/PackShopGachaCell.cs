using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.PackShopGacha.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PackShopGacha.Presentation.Views
{
    public class PackShopGachaCell : UICollectionViewCell
    {
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] Button _bannerButton;
        [SerializeField] Button _infoButton;

        public void Setup(PackShopGachaCellViewModel model, Action<MasterDataId> onTappedBannerAction)
        {
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, model.GachaBannerAssetPath.Value);
            _bannerButton.onClick.AddListener(() => onTappedBannerAction?.Invoke(model.GachaId));
            _infoButton.onClick.AddListener(() => onTappedBannerAction?.Invoke(model.GachaId));
        }
    }
}