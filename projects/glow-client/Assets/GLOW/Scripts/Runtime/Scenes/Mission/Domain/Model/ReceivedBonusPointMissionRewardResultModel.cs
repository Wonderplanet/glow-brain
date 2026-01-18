using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.Mission.Domain.Model
{
    public record ReceivedBonusPointMissionRewardResultModel(
        UserMissionBonusPointModel BeforeMissionBonusPointModel,
        UserMissionBonusPointModel UpdatedMissionBonusPointModel,
        IReadOnlyList<CommonReceiveResourceModel> ReceivedBonusPointMissionRewards,
        IReadOnlyList<MasterDataId> ReceivedBonusPointRewardIds)
    {
        public static ReceivedBonusPointMissionRewardResultModel Empty { get; } = new(
            UserMissionBonusPointModel.Empty,
            UserMissionBonusPointModel.Empty,
            new List<CommonReceiveResourceModel>(),
            new List<MasterDataId>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
