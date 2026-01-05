using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public record BulkReceiveMissionRewardUseCaseModel(
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        MissionFetchResultModel MissionFetchResultModel);
}
