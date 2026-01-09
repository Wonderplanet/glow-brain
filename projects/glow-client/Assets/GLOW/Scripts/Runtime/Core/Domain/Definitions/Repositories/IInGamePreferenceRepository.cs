using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Core.Domain.Repositories
{
    public interface IInGamePreferenceRepository
    {
        BattleSpeed InGameBattleSpeed { get; set; }
        InGameAutoEnabledFlag IsInGameAutoEnabled { get; set; }
        InGameContinueSelectingFlag IsInGameContinueSelecting { get; set; }
    }
}