using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Domain.Models.Stage
{
    public record StageContinueDiamondResultModel(
        UserParameterModel UserParameterModel,
        ContinueCount ContinueCount
    );
}
