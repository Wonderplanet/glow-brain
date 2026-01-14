using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.UseCases
{
    //SceneViewContentCategory.EventStage...MstId -> MstQuestGroupId
    //SceneViewContentCategory.Pvp...MstId -> SysPvpSeasonModel.Id
    public record ResumableStateModel(
        SceneViewContentCategory Category,
        MasterDataId MstId,
        MasterDataId MstEventId)
    {
        public static ResumableStateModel Empty { get; } = new ResumableStateModel(
            SceneViewContentCategory.None,
            MasterDataId.Empty,
            MasterDataId.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsOpenEvent()
        {
            return Category == SceneViewContentCategory.EventStage && !MstEventId.IsEmpty();
        }
    };
}
