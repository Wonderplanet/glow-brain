using System;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Component
{
    public class AnnouncementTextCellComponent : UIObject
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
        [SerializeField] UIText _bannerText;
        [SerializeField] Button _button;
        
        public Button.ButtonClickedEvent OnSelected => _button.onClick;

        public void Setup(AnnouncementCellViewModel viewModel)
        {
            _dateText.SetText(viewModel.AnnouncementStartAt.ToFormattedString());
            _bannerText.SetText(viewModel.AnnouncementTitle.Value);

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
    }
}