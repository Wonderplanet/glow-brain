using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class FestivalGachaBannerComponent : UIObject
    {
        [SerializeField] UIObject _bannerImageParent;
        [SerializeField] UIText _remainingTime;
        [SerializeField] UIText _thresholdText;
        [SerializeField] UIImage _notificationBadgeIcon;
        [SerializeField] GameObject _limitlessIcon;
        [SerializeField] GameObject _remainingTextParent;
        [SerializeField] Button _transitionGachaContentButton;
        [SerializeField] Button _infoButton;
        [SerializeField] RectTransform _rectTransform;

        MasterDataId _mstGachaId;
        public MasterDataId MstGachaId => _mstGachaId;
        public Action<MasterDataId> OnTappedBanner { private get; set; }
        public Action<MasterDataId> OnInfoButton { private get; set; }

        public void Setup(FestivalGachaBannerViewModel model)
        {
            _mstGachaId = model.MstGachaId;
            
            _notificationBadgeIcon.gameObject.SetActive(model.NotificationBadge.Value);

            _limitlessIcon.SetActive(model.GachaRemainingTimeText == GachaRemainingTimeText.Empty);
            _remainingTextParent.SetActive(model.GachaRemainingTimeText != GachaRemainingTimeText.Empty);

            if (model.GachaRemainingTimeText != GachaRemainingTimeText.Empty)
            {
                _remainingTime.SetText(model.GachaRemainingTimeText.Value);
            }

            _transitionGachaContentButton.onClick.AddListenerAsExclusive(() => OnTappedBanner.Invoke(model.MstGachaId));
            _infoButton.onClick.AddListenerAsExclusive(() => OnInfoButton.Invoke(model.MstGachaId));
            
            _thresholdText.SetText(model.GachaThresholdText.Value);
        }

        public void SetGachaBannerImage(FestivalGachaBannerImageComponent component)
        {
            Instantiate(component, _bannerImageParent.transform);
        }

        public void RefreshScale()
        {
            // 生成時にzスケールが0になりパーティクル再生されないため、1に固定する
            _rectTransform.localScale = Vector3.one;
        }
    }
}