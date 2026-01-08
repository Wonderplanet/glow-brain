using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public interface IDeckAutoPlayerSummonPriorityCalculator
    {
        void Initialize(IReadOnlyList<DeckUnitModel> deckUnits);

        DeckAutoPlayerSummonPriority CalculatePriority(
            DeckUnitModel deckUnit,
            int summoningCount,
            Dictionary<CharacterUnitRoleType, int> summoningCountDictionary);
    }
}