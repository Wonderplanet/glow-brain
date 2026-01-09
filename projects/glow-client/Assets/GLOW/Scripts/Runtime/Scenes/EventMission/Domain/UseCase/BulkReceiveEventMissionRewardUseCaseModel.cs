using System.Collections.Generic;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.EventMission.Domain.Model;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public record BulkReceiveEventMissionRewardUseCaseModel(
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        EventMissionFetchResultModel EventMissionFetchResultModel);
}
