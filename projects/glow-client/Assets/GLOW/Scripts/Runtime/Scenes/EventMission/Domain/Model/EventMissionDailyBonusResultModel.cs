using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionDailyBonusResultModel(
        LoginDayCount ProgressLoginDayCount,
        IReadOnlyList<EventMissionDailyBonusCellModel> EventMissionDailyBonusCellModels,//こっちにもCommonReceiveResourceModelがいるが、表示用に留める
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels)// 汎用報酬受取り時はこちらを使う
    {
        public static EventMissionDailyBonusResultModel Empty { get; } = new(
            new LoginDayCount(0), 
            new List<EventMissionDailyBonusCellModel>(), 
            new List<CommonReceiveResourceModel>());
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
