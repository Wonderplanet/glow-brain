using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models.Stage
{
    public record StageContinueAdResultModel(
        ContinueCount ContinueCount,
        ContinueCount ContinueAdCount);
}
