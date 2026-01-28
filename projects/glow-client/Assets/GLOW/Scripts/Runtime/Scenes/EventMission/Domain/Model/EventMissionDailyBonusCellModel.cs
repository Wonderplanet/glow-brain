using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionDailyBonusCellModel(
        MasterDataId Id,
        DailyBonusReceiveStatus DailyBonusReceiveStatus,
        LoginDayCount LoginDayCount,
        CommonReceiveResourceModel CommonReceiveResourceModel,
        SortOrder SortOrder)
    {
        public static EventMissionDailyBonusCellModel Empty { get; } = new(
            MasterDataId.Empty,
            DailyBonusReceiveStatus.Nothing,
            new LoginDayCount(0),
            CommonReceiveResourceModel.Empty,
            new SortOrder(0));
    }
}
