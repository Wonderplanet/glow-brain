using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models
{
    public interface IStageClearCountable
    {
        MasterDataId MstStageId { get; }
        StageClearCount ClearCount { get; }
    }
}