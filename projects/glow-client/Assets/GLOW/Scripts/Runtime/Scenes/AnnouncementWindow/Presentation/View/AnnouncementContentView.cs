using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AnnouncementWindow.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementContentView : UIView
    {
        [SerializeField] GameObject _scrollContainer;
        [SerializeField] AnnouncementBannerCellComponent _bannerCellComponentM;
        [SerializeField] AnnouncementBannerCellComponent _bannerCellComponentL;
        [SerializeField] AnnouncementTextCellComponent _textCellComponent;
        [SerializeField] ChildScaler _childScaler;
        
        Transform ScrollContainerTransform => _scrollContainer.transform;
        
        public AnnouncementBannerCellComponent InstantiateBannerCellComponentM()
        {
            return Instantiate(_bannerCellComponentM, ScrollContainerTransform);
        }
        
        public AnnouncementBannerCellComponent InstantiateBannerCellComponentL()
        {
            return Instantiate(_bannerCellComponentL, ScrollContainerTransform);
        }
        
        public AnnouncementTextCellComponent InstantiateTextCellComponent()
        {
            return Instantiate(_textCellComponent, ScrollContainerTransform);
        }
        
        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }
    }
}