using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Scenes.Notice.Presentation.ViewModel
{
    public record NoticeViewModel(
        NoticeId NoticeId,
        IgnDisplayType ViewType,
        IgnDisplayFrequencyType DisplayFrequencyType,
        NoticeTitle Title,
        NoticeMessage Message,
        NoticeBannerUrl BannerUrl,
        NoticeDestinationType DestinationType,
        DestinationScene DestinationScene,
        NoticeDestinationPathDetail NoticeDestinationPathDetail,
        NoticeTransitionButtonText TransitionButtonText);
}