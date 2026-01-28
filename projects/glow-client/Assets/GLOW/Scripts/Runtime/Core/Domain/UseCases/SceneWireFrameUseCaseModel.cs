using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Core.Domain.UseCases
{
    public record SceneWireFrameUseCaseModel(
        SceneViewContentCategory Category,
        MasterDataId MstId,
        MasterDataId MstEventId,
        QuestOpenStatus OpenStatus
    )
    {
        public static SceneWireFrameUseCaseModel Empty { get; } = new SceneWireFrameUseCaseModel(
            SceneViewContentCategory.None,
            MasterDataId.Empty,
            MasterDataId.Empty,
            QuestOpenStatus.NotOpenQuest
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsOpenEvent()
        {
            return Category == SceneViewContentCategory.EventStage && !MstEventId.IsEmpty();
        }

        public bool IsOpenPvp()
        {
            return Category == SceneViewContentCategory.Pvp && !MstId.IsEmpty();
        }

        public bool IsOpenAdventBattle()
        {
            return Category == SceneViewContentCategory.AdventBattle && !MstId.IsEmpty();
        }
    };
}
