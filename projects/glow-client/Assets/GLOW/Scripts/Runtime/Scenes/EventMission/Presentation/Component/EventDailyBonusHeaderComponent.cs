using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.EventMission.Presentation.Component
{
    public class EventDailyBonusHeaderComponent : UIComponent
    {
        [SerializeField] UIImage _missionBannerImage;
        [SerializeField] UIImage _dailyBonusBannerImage;
        [SerializeField] RemainingTimeAreaComponent _remainingTimeAreaComponent;

        public RemainingTimeAreaComponent RemainingTimeAreaComponent => _remainingTimeAreaComponent;

        public void UpdateBannerVisible(bool isDailyBonus)
        {
            _missionBannerImage.IsVisible = !isDailyBonus;
            _dailyBonusBannerImage.IsVisible = isDailyBonus;
        }

        public void SetUpDailyBonusBannerImage(EventMissionDailyBonusBannerAssetPath assetPath, bool visibleAfterLoaded)
        {
            // TODO: 表示がないときのデザイン確認
            _dailyBonusBannerImage.gameObject.SetActive(!assetPath.IsEmpty());
            if(assetPath.IsEmpty()) return;

            SpriteLoaderUtil.Clear(_dailyBonusBannerImage.Image);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _dailyBonusBannerImage.Image,
                assetPath.Value,
                () =>
                {
                    if (!_dailyBonusBannerImage) return;
                    _dailyBonusBannerImage.IsVisible = visibleAfterLoaded;
                });
        }

        public void SetUpMissionBannerImage(EventMissionBannerAssetPath assetPath, bool visibleAfterLoaded)
        {
            // TODO: 表示がないときのデザイン確認
            _missionBannerImage.gameObject.SetActive(!assetPath.IsEmpty());
            if(assetPath.IsEmpty()) return;

            SpriteLoaderUtil.Clear(_missionBannerImage.Image);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _missionBannerImage.Image,
                assetPath.Value,
                () =>
                {
                    if (!_missionBannerImage) return;
                    _missionBannerImage.IsVisible = visibleAfterLoaded;
                });
        }
    }
}
