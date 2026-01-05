using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    /// <summary> TickCountと制限時間の更新をした結果を返す </summary>
    public class StageTimeUpdateProcess : IStageTimeUpdateProcess
    {
        StageTimeModel IStageTimeUpdateProcess.UpdateStageTime(
            StageTimeModel stageTimeModel,
            TickCount tickCount)
        {
            var currentTickCount = stageTimeModel.CurrentTickCount + tickCount;
            var remainingTime = stageTimeModel.RemainingTime;
            if (stageTimeModel.HasTimeLimit)
            {
                remainingTime -= tickCount;
            }

            var remainingTimeTextColor = stageTimeModel.IsShowCountDown
                ? RemainingTimeTextColor.GetColor(remainingTime.IsHighlightTextTime())
                : stageTimeModel.RemainingTimeTextColor;

            return stageTimeModel with
            {
                CurrentTickCount = currentTickCount,
                RemainingTime = remainingTime,
                RemainingTimeTextColor = remainingTimeTextColor,
            };
        }
    }
}
