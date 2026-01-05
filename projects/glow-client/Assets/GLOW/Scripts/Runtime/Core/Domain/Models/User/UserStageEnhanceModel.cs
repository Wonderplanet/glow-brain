using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserStageEnhanceModel(
        MasterDataId MstStageId,
        EnhanceQuestChallengeCount ResetChallengeCount,
        EnhanceQuestChallengeCount ResetAdChallengeCount,
        EnhanceQuestScore MaxScore)
    {
        public static UserStageEnhanceModel Empty { get; } = new UserStageEnhanceModel(
            MasterDataId.Empty,
            EnhanceQuestChallengeCount.Empty,
            EnhanceQuestChallengeCount.Empty,
            EnhanceQuestScore.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
