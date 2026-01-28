using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Scenes.Notice.Domain.Model
{
    public record NoticeModel(
        NoticeId NoticeId,
        IgnDisplayType ViewType,
        IgnDisplayFrequencyType DisplayFrequencyType,
        NoticeTitle Title,
        NoticeMessage Message,
        NoticeBannerUrl BannerUrl,
        NoticeDestinationType DestinationType,
        DestinationScene DestinationScene,
        NoticeDestinationPathDetail NoticeDestinationPathDetail,
        NoticeTransitionButtonText TransitionButtonText)
    {
        public static NoticeModel Empty { get; } = new NoticeModel(
            NoticeId.Empty,
            IgnDisplayType.BasicBanner,
            IgnDisplayFrequencyType.Once,
            NoticeTitle.Empty,
            NoticeMessage.Empty,
            NoticeBannerUrl.Empty,
            NoticeDestinationType.Empty,
            DestinationScene.Empty,
            NoticeDestinationPathDetail.Empty,
            NoticeTransitionButtonText.Empty);
    }
}