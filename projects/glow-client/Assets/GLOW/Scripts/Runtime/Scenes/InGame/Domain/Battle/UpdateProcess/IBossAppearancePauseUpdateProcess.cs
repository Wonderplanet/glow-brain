using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IBossAppearancePauseUpdateProcess
    {
        BossAppearancePauseModel Update(
            BossAppearancePauseModel bossAppearancePause,
            TickCount tickCount);
    }
}
