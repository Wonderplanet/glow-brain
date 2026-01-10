using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class BossAppearancePauseUpdateProcess : IBossAppearancePauseUpdateProcess
    {
        public BossAppearancePauseModel Update(
            BossAppearancePauseModel bossAppearancePause,
            TickCount tickCount)
        {
            if (bossAppearancePause.IsEmpty()) return bossAppearancePause;

            var remainingPauseFrames = bossAppearancePause.RemainingPauseFrames - tickCount;
            if (remainingPauseFrames.IsZero())
            {
                return BossAppearancePauseModel.Empty;
            }

            var updatedBossAppearancePause = bossAppearancePause with
            {
                RemainingPauseFrames = remainingPauseFrames
            };

            return updatedBossAppearancePause;
        }
    }
}
