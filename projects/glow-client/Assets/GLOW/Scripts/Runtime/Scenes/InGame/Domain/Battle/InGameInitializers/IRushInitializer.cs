using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IRushInitializer
    {
        RushInitializerResult Initialize(
            QuestType questType,
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement);
    }
}
