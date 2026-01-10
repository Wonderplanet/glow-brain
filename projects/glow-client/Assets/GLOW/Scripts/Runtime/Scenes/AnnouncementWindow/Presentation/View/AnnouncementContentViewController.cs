using System;
using System.Collections.Generic;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Presentation.Component;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-3_お知らせ
    /// 　　121-3-1_お知らせ
    /// </summary>
    public class AnnouncementContentViewController : UIViewController<AnnouncementContentView>
    {
        public record Argument(
            IReadOnlyList<AnnouncementCellViewModel> ViewModels, HookedPatternUrl HookedPatternUrlInAnnouncements);
        [Inject] IAnnouncementContentViewDelegate ViewDelegate { get; }
        
        public Action<AnnouncementId> OnReadAnnouncement { private get; set; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public void SetViewModel(
            IReadOnlyList<AnnouncementCellViewModel> viewModels)
        {
            foreach (var cell in viewModels)
            {
                if (cell.InformationCellType == AnnouncementCellType.Banner)
                {
                    var instance = CreateBannerCellComponent(cell.AnnouncementBannerUrl);
                    instance.Setup(cell);
                    instance.SetNoticeBadgeVisible(!cell.IsRead);
                    instance.OnSelected.AddListener(() =>
                    {
                        // お知らせを開いたら既読としてバッジを消す
                        instance.SetNoticeBadgeVisible(false);
                        OnBannerCellSelected(cell);
                    });
                }
                else
                {
                    var instance = ActualView.InstantiateTextCellComponent();
                    instance.Setup(cell);
                    instance.SetNoticeBadgeVisible(!cell.IsRead);
                    instance.OnSelected.AddListener(() =>
                    {
                        // お知らせを開いたら既読としてバッジを消す
                        instance.SetNoticeBadgeVisible(false);
                        OnBannerCellSelected(cell);
                    });
                }
            }
        }
        
        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }
        
        void OnBannerCellSelected(
            AnnouncementCellViewModel viewModel)
        {
            ViewDelegate.OnBannerCellSelected(viewModel.AnnouncementContentsUrl);
            OnReadAnnouncement?.Invoke(viewModel.AnnouncementId);
        }
        
        AnnouncementBannerCellComponent CreateBannerCellComponent(AnnouncementBannerUrl url)
        {
            return url.GetBannerSizeType() == AnnouncementBannerSizeType.SizeL ? ActualView.InstantiateBannerCellComponentL() : ActualView.InstantiateBannerCellComponentM(); 
        }
    }
}