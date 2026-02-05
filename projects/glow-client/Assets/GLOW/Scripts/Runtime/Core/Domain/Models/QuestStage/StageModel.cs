using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public record StageModel(
        UserDataId UserDataId,
        MasterDataId MstStageId,
        StageReleaseStatus Status,
        EventClearTimeMs ClearTimeMs,
        StageClearCount ClearCount) : IStageClearCountable
    {
        public static StageModel Empty { get; } = new StageModel(
            UserDataId.Empty,
            MasterDataId.Empty,
            StageReleaseStatus.Empty,
            EventClearTimeMs.Empty,
            StageClearCount.Empty);

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };

}

