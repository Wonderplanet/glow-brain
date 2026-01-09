using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public record DebugStageUseCaseModel(MstStageModel MstStageModel, Difficulty Difficulty);
}