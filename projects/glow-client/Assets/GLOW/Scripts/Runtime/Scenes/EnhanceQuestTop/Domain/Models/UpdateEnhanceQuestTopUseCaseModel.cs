
namespace GLOW.Scenes.EnhanceQuestTop.Domain.Models
{
    public record UpdateEnhanceQuestTopUseCaseModel(QuestPartyModel QuestPartyModel)
    {
        public static UpdateEnhanceQuestTopUseCaseModel Empty { get; } = new (QuestPartyModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
