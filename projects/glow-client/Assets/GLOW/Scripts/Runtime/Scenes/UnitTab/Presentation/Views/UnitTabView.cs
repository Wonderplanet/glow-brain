using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitTab.Presentation.Views
{
    public class UnitTabView : UIView
    {
        [SerializeField] RectTransform _contentRoot;
        [SerializeField] UIToggleableComponentGroup _tabGroup;
        [SerializeField] RectTransform _background;
        [SerializeField] GameObject _unitListBadge;
        [SerializeField] GameObject _outpostEnhanceBadge;

        public RectTransform ContentRoot => _contentRoot;

        public void SetBadge(NotificationBadge unitList, NotificationBadge outpostEnhance)
        {
            _unitListBadge.SetActive(unitList.Value);
            _outpostEnhanceBadge.SetActive(outpostEnhance.Value);
        }

        public void SetTabOn(string key)
        {
            _tabGroup.SetToggleOn(key);
        }

        public void SetBackgroundRectTop(float top)
        {
            _background.offsetMax = new Vector2(_background.offsetMax.x, -top);
        }
    }
}
