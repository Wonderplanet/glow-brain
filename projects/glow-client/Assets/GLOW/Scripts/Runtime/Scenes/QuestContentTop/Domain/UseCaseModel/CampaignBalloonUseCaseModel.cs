using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record CampaignBalloonUseCaseModel(
        CampaignBalloonType Type,
        CampaignBalloonAdditionValue AdditionValue,
        CampaignBalloonLimitTime LimitTime)
    {
        public static CampaignBalloonUseCaseModel Empty { get; } = new CampaignBalloonUseCaseModel(
            CampaignBalloonType.Item,
            CampaignBalloonAdditionValue.Empty,
            CampaignBalloonLimitTime.Empty
        );

        public bool IsEmpty => ReferenceEquals(this, Empty);
    }
}