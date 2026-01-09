using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class GachaBannerSizeLComponent : UIObject
    {
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] UIText _remainingTime;
        [SerializeField] UIText _descriptionText;
        [SerializeField] UIText _thresholdText;
        [SerializeField] UIImage _notificationBadgeIcon;
        [SerializeField] GameObject _limitlessIcon;
        [SerializeField] GameObject _remainingTextParent;
        [SerializeField] GameObject _gachaBackgroundPurpleParent;
        [SerializeField] GameObject _gachaBackgroundYellowParent;
        [SerializeField] GameObject _gachaBackgroundGreenParent;
        [SerializeField] GameObject _gachaBackgroundBlueParent;
        [SerializeField] Button _transitionGachaContentButton;
        [SerializeField] Button _infoButton;

        MasterDataId _gachaId;
        public MasterDataId GachaId => _gachaId;
        public Action<MasterDataId> OnTappedBanner { private get; set; }
        public Action<MasterDataId> OnInfoButton { private get; set; }

        public void Setup(GachaBannerViewModel model)
        {
            _gachaId = model.GachaId;
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, model.GachaBannerAssetPath.Value);
            _notificationBadgeIcon.gameObject.SetActive(model.NotificationBadge.Value);

            _limitlessIcon.SetActive(model.GachaRemainingTimeText == GachaRemainingTimeText.Empty);
            _remainingTextParent.SetActive(model.GachaRemainingTimeText != GachaRemainingTimeText.Empty);

            if (model.GachaRemainingTimeText != GachaRemainingTimeText.Empty)
            {
                _remainingTime.SetText(model.GachaRemainingTimeText.Value);
            }

            _transitionGachaContentButton.onClick.AddListenerAsExclusive(() => OnTappedBanner.Invoke(model.GachaId));
            _infoButton.onClick.AddListenerAsExclusive(() => OnInfoButton.Invoke(model.GachaId));
            
            _descriptionText.SetText(model.GachaDescription.Value);
            _thresholdText.SetText(model.GachaThresholdText.Value);
        }
    }
}
