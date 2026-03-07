using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.EventMission.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model;

namespace GLOW.Scenes.EventMission.Domain.UseCase
{
    public record ReceiveEventMissionRewardUseCaseModel(
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        EventMissionFetchResultModel EventMissionFetchResultModel);
}
