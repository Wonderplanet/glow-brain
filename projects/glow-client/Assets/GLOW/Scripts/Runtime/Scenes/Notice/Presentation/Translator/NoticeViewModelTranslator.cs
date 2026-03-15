using GLOW.Scenes.Notice.Domain.Model;
using GLOW.Scenes.Notice.Presentation.ViewModel;

namespace GLOW.Scenes.Notice.Presentation.Translator
{
    public class NoticeViewModelTranslator
    {
        public static NoticeViewModel ToInGameNoticeViewModel(NoticeModel model)
        {
            return new NoticeViewModel(
                model.NoticeId,
                model.ViewType,
                model.DisplayFrequencyType,
                model.Title,
                model.Message,
                model.BannerUrl,
                model.DestinationType,
                model.DestinationScene,
                model.NoticeDestinationPathDetail,
                model.TransitionButtonText);

        }
    }
}