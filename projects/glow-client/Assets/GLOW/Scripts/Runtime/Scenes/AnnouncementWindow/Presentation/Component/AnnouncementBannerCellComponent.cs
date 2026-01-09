using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Component
{
    public class AnnouncementBannerCellComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct CellHeader
        {
            public AnnouncementCategory Category;
            public GameObject HeaderComponent;
        }
        [SerializeField] CellHeader[] _cellHeaders;
        [SerializeField] UIText _dateText;
        [SerializeField] UIImage _noticeBadgeIcon;
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] Button _button;
        
        public Button.ButtonClickedEvent OnSelected => _button.onClick;

        public void Setup(AnnouncementCellViewModel viewModel)
        {
            _dateText.SetText(viewModel.AnnouncementStartAt.ToFormattedString());

            SetupDownloadBannerImage(viewModel.AnnouncementBannerUrl);
            SetHeader(viewModel.AnnouncementCategory);
        }
        
        public void SetNoticeBadgeVisible(bool isVisible)
        {
            _noticeBadgeIcon.IsVisible = isVisible;
        }
        
        void SetHeader(AnnouncementCategory category)
        {
            foreach (var cellHeader in _cellHeaders)
            {
                cellHeader.HeaderComponent.SetActive(cellHeader.Category == category);
            }
        }
        
        void SetupDownloadBannerImage(AnnouncementBannerUrl bannerUrl)
        {
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, bannerUrl.Value);
        }
    }
}