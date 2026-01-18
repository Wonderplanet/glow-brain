using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.Mission.Domain.Model
{
    public record ReceivedDailyBonusInfoModel(
        MissionFetchResultModel MissionFetchResult,
        LoginDayCount LoginDayCount,
        IReadOnlyList<CommonReceiveResourceModel> DailyBonusRewards)
    {
        public static ReceivedDailyBonusInfoModel Empty { get; } = new(
            MissionFetchResultModel.Empty,
            LoginDayCount.Empty,
            new List<CommonReceiveResourceModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
