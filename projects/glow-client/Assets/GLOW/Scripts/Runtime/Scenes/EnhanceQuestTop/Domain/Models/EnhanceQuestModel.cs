using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.EnhanceQuestTop.Domain.Models
{
    public record EnhanceQuestModel(
        MstQuestModel MstQuest,
        MstStageModel MstStage
    )
    {
        public static EnhanceQuestModel Empty { get; } = new EnhanceQuestModel(
            MstQuestModel.Empty,
            MstStageModel.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

