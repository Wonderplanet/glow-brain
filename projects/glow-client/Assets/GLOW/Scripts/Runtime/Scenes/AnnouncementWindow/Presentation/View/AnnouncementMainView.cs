using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AnnouncementWindow.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementMainView : UIView
    {
        [SerializeField] Transform _contentRoot;
        [SerializeField] UIToggleableComponentGroup _tab;
        [SerializeField] UIImage _eventTabBadge;
        [SerializeField] UIImage _operationTabBadge;
        [SerializeField] UIObject _indicator;
        [SerializeField] AnnouncementBannerCellComponent _bannerCellComponentM;
        [SerializeField] AnnouncementBannerCellComponent _bannerCellComponentL;
        [SerializeField] AnnouncementTextCellComponent _textCellComponent;
        [SerializeField] Button _eventButton;
        [SerializeField] Button _operationButton;
        
        public Transform ContentRoot => _contentRoot;
        
        public UIObject Indicator => _indicator;
        
        public void SetToggleOn(AnnouncementTabType type)
        {
            _tab.SetToggleOn(type.ToString());
        }
        
        public void SetEventBadgeVisible(bool visible)
        {
            _eventTabBadge.Hidden = !visible;
        }
        
        public void SetOperationBadgeVisible(bool visible)
        {
            _operationTabBadge.Hidden = !visible;
        }
        
        public void SetButtonInteractable(bool interactable)
        {
            _eventButton.interactable = interactable;
            _operationButton.interactable = interactable;
        }
        
        public AnnouncementBannerCellComponent InstantiateBannerCellComponentM()
        {
            return Instantiate(_bannerCellComponentM, ContentRoot);
        }
        
        public AnnouncementBannerCellComponent InstantiateBannerCellComponentL()
        {
            return Instantiate(_bannerCellComponentL, ContentRoot);
        }
        
        public AnnouncementTextCellComponent InstantiateTextCellComponent()
        {
            return Instantiate(_textCellComponent, ContentRoot);
        }
        
        public void ClearContents()
        {
            foreach (Transform child in ContentRoot)
            {
                Destroy(child.gameObject);
            }
        }
    }
}