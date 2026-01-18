using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IStageTimeUpdateProcess
    {
        StageTimeModel UpdateStageTime(
            StageTimeModel stageTimeModel,
            TickCount tickCount);
    }
}
