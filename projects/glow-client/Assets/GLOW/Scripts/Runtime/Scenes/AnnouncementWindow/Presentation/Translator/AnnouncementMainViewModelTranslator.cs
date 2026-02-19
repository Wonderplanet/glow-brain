using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;
using GLOW.Scenes.AnnouncementWindow.Presentation.ViewModel;

namespace GLOW.Scenes.AnnouncementWindow.Presentation.Translator
{
    public class AnnouncementMainViewModelTranslator
    {
        public static AnnouncementMainViewModel ToAnnouncementMainViewModel(
            FetchAnnouncementListModel fetchAnnouncementListModel)
        {
            var eventViewModel = new AnnouncementEventViewModel(
                fetchAnnouncementListModel.Announcements.Where(model => model.AnnouncementTabType == AnnouncementTabType.Event).Select(model => new AnnouncementCellViewModel(
                    model.AnnouncementId,
                    model.AnnouncementCellType,
                    model.AnnouncementCategory,
                    model.AnnouncementStartAt,
                    model.AnnouncementTitle,
                    model.AnnouncementBannerUrl,
                    model.AnnouncementStatus,
                    model.AnnouncementContentsUrl,
                    model.IsRead)).ToList());
            
            var operationViewModel = new AnnouncementOperationViewModel(
                fetchAnnouncementListModel.Announcements.Where(model => model.AnnouncementTabType == AnnouncementTabType.Operation).Select(model => new AnnouncementCellViewModel(
                    model.AnnouncementId,
                    model.AnnouncementCellType,
                    model.AnnouncementCategory,
                    model.AnnouncementStartAt,
                    model.AnnouncementTitle,
                    model.AnnouncementBannerUrl,
                    model.AnnouncementStatus,
                    model.AnnouncementContentsUrl,
                    model.IsRead)).ToList());
            
            return new AnnouncementMainViewModel(
                eventViewModel, 
                operationViewModel, 
                fetchAnnouncementListModel.HookedPatternUrlInAnnouncements);
        }
    }
}