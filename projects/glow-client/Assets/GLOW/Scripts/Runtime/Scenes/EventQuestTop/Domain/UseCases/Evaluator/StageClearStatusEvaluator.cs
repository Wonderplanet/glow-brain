using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public static class StageClearStatusEvaluator
    {
        public static StageClearStatus Evaluate(
            MstStageEventSettingModel mstStageEventSettingModel,
            UserStageEventModel userModel,
            bool isReleased)
        {
            if (!isReleased) return StageClearStatus.None;
            if (IsOncePerDayStage(mstStageEventSettingModel)) return StageClearStatus.Daily;

            var isNew = userModel.IsEmpty();
            var isClear = !userModel.IsEmpty() && 1 <= userModel.TotalClearCount;
            if(isClear) return StageClearStatus.Clear;
            if(isNew) return StageClearStatus.New;
            return StageClearStatus.None;
        }

        static bool IsOncePerDayStage(MstStageEventSettingModel mstStageEventSettingModel)
        {
            if (mstStageEventSettingModel.ResetType == null) return false;

            return mstStageEventSettingModel.ResetType == ResetType.Daily &&
                   mstStageEventSettingModel.ClearableCount.Value == 1;
        }
    }
}
