using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IDeckInitializer
    {
        DeckInitializerResult Initialize(
            OutpostEnhancementModel outpostEnhancementModel,
            OutpostEnhancementModel pvpOpponentOutpostEnhancementModel,
            ArtworkEffectModel artworkEffectModel,
            ArtworkEffectModel pvpOpponentArtworkEffectModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
