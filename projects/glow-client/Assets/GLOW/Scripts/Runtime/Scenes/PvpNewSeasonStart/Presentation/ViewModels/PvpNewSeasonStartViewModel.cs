using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;

namespace GLOW.Scenes.PvpNewSeasonStart.Presentation.ViewModels
{
    public record PvpNewSeasonStartViewModel(
        PvpRankClassType PvpRankClassType,
        ScoreRankLevel ScoreRankLevel
        )
    {
    }
}

