using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public interface IDeckAutoPlayerSummonSelector
    {
        void UpdateSummonState(DeckUnitModel selectedUnit, bool isUnitSummoned);
        DeckUnitModel GetSummonDeckUnit(
            IReadOnlyList<DeckUnitModel> summonCandidateDeckUnits,
            int summoningCount);
    }
}
