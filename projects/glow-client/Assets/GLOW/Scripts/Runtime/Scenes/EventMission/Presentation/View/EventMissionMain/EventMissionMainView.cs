using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.QuestContent;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Views.Interaction;
using GLOW.Scenes.EventMission.Presentation.Component;
using GLOW.Scenes.Mission.Presentation.Extension;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EventMission.Presentation.View.EventMissionMain
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-6_イベントミッション
    /// </summary>
    public class EventMissionMainView : UIView
    {
        [SerializeField] UIImage _eventLoginBonusBadge;
        [SerializeField] UIImage _eventAchievementBadge;
        [SerializeField] UIText _titleText;
        [SerializeField] ScreenActivityIndicatorView _indicator;
        [SerializeField] Transform _contentRoot;
        [SerializeField] UIToggleableComponentGroup _tabGroup;
        [SerializeField] Button _bulkReceiveButton;
        [SerializeField] Button _closeButton;
        [SerializeField] EventDailyBonusHeaderComponent _headerComponent;
        
        public ScreenActivityIndicatorView Indicator => _indicator;
        public Transform ContentRoot => _contentRoot;
        public UIToggleableComponentGroup TabGroup => _tabGroup;
        
        public void SetToggleOn(MissionType missionType)
        {
            _tabGroup.SetToggleOn(missionType.ToString());
        }

        public void SetTitle(MissionType missionType)
        {
            _titleText.SetText(MissionTypeExtension.MissionTypeToMissionTypeName(missionType));
        }
        
        public void SetBadgeVisible(MissionType type, bool visible)
        {
            switch (type)
            {
                case MissionType.Event:
                    _eventAchievementBadge.Hidden = !visible;
                    break;
                default:
                    break;
            }
        }
        
        public void SetBulkReceiveButtonInteractable(bool interactable)
        {
            _bulkReceiveButton.interactable = interactable;
        }
        
        public void SetCloseButtonInteractable(bool interactable)
        {
            _closeButton.interactable = interactable;
        }
        
        public void SetBulkReceiveButtonVisible(bool visible)
        {
            _bulkReceiveButton.gameObject.SetActive(visible);
        }
        
        public void SetUpHeaderDailyBonusBannerImage(EventMissionDailyBonusBannerAssetPath assetPath, bool visibleAfterLoaded)
        {
            _headerComponent.SetUpDailyBonusBannerImage(assetPath, visibleAfterLoaded);
        }
        
        public void SetUpHeaderMissionBannerImage(EventMissionBannerAssetPath assetPath, bool visibleAfterLoaded)
        {
            _headerComponent.SetUpMissionBannerImage(assetPath, visibleAfterLoaded);
        }
        
        public void UpdateBannerVisible(bool isDailyBonus)
        {
            _headerComponent.UpdateBannerVisible(isDailyBonus);
        }

        public void SetUpHeaderRemainingTime(RemainingTimeSpan remainingEventTimeSpan)
        {
            // 残り時間がEmptyの場合は何も表示しない
            var timeSpanText = remainingEventTimeSpan.IsEmpty()
                ? ""
                : TimeSpanFormatter.FormatUntilEnd(remainingEventTimeSpan);
            
            _headerComponent.RemainingTimeAreaComponent.RemainingTimeTexts[0].SetText(timeSpanText);
        }
    }
}