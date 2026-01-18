using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Notice;

namespace GLOW.Core.Data.Translators
{
    public static class OprInGameNoticeDataTranslator
    {
        public static OprNoticeModel ToOprInGameNoticeModel(OprInGameNoticeData oprInGameNoticeData)
        {
            return new OprNoticeModel(
                new NoticeId(oprInGameNoticeData.Id),
                oprInGameNoticeData.DisplayType,
                new NoticeDestinationType(oprInGameNoticeData.DestinationType),
                new NoticeDestinationPath(oprInGameNoticeData.DestinationPath),
                string.IsNullOrEmpty(oprInGameNoticeData.DestinationPathDetail)
                    ? NoticeDestinationPathDetail.Empty
                    : new NoticeDestinationPathDetail(oprInGameNoticeData.DestinationPathDetail),
                new NoticePriority(oprInGameNoticeData.Priority),
                oprInGameNoticeData.DisplayFrequencyType,
                new NoticeTitle(oprInGameNoticeData.Title),
                new NoticeMessage(oprInGameNoticeData.Description),
                string.IsNullOrEmpty(oprInGameNoticeData.BannerUrl)
                    ? NoticeBannerUrl.Empty
                    : new NoticeBannerUrl(oprInGameNoticeData.BannerUrl),
                string.IsNullOrEmpty(oprInGameNoticeData.ButtonTitle)
                    ? NoticeTransitionButtonText.Empty
                    : new NoticeTransitionButtonText(oprInGameNoticeData.ButtonTitle));
        }
    }
}
