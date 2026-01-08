using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprNoticeModel(
        NoticeId Id,
        IgnDisplayType DisplayType,
        NoticeDestinationType DestinationType,
        NoticeDestinationPath DestinationPath,
        NoticeDestinationPathDetail DestinationPathDetail,
        NoticePriority Priority,
        IgnDisplayFrequencyType DisplayFrequencyType,
        NoticeTitle Title,
        NoticeMessage Message,
        NoticeBannerUrl BannerUrl,
        NoticeTransitionButtonText TransitionButtonName)
    {
        public static OprNoticeModel Empty { get; } = new OprNoticeModel(
            new NoticeId(""),
            IgnDisplayType.BasicBanner,
            new NoticeDestinationType(""),
            new NoticeDestinationPath(""),
            new NoticeDestinationPathDetail(""),
            new NoticePriority(0),
            IgnDisplayFrequencyType.Once,
            new NoticeTitle(""),
            new NoticeMessage(""),
            new NoticeBannerUrl(""),
            new NoticeTransitionButtonText(""));
    }
}