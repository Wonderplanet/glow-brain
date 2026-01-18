using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Mission.Domain.Model;

namespace GLOW.Scenes.AdventBattleMission.Domain.Model
{
    public record AdventBattleMissionReceiveResultModel(
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels,
        AdventBattleMissionFetchResultModel AdventBattleMissionFetchResultModel)
    {
        public static AdventBattleMissionReceiveResultModel Empty { get; } =
            new(new List<CommonReceiveResourceModel>(), AdventBattleMissionFetchResultModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
