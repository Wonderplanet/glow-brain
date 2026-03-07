using System.Collections.Generic;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record QuestContentTopElementUseCaseModel(
        QuestContentTopElementType ElementType,
        QuestContentOpeningStatusModel OpeningStatusModel,
        IQuestChallengeCountable ChallengeCount,
        QuestContentTopChallengeType ChallengeType,
        QuestChallengeResetTime ChallengeResetTime,
        RemainingTimeSpan RemainingTimeSpan,//降臨バトルに関しては、QuestContentOpeningStatus.BeforeOpenのときは開催までの時間が入る
        HasRankingFlag HasRanking,
        NotificationBadge HasRankingNotification,
        NotificationBadge HasBannerBadgeNotification,
        MasterDataId MstEventId,
        EventName EventName,
        EventContentBannerAssetPath BannerAssetPath,
        IReadOnlyList<CampaignModel> CampaignModels)
    {
        public static QuestContentTopElementUseCaseModel Empty { get; } = new(
            QuestContentTopElementType.Other,
            QuestContentOpeningStatusModel.Empty,
            EnhanceQuestChallengeCount.Empty,
            QuestContentTopChallengeType.Normal,
            QuestChallengeResetTime.Empty,
            RemainingTimeSpan.Empty,
            HasRankingFlag.False,
            NotificationBadge.False,
            NotificationBadge.False,
            MasterDataId.Empty,
            EventName.Empty,
            EventContentBannerAssetPath.Empty,
            new List<CampaignModel>()
        );

        public bool IsEmpty => ReferenceEquals(this, Empty);
    };
}
