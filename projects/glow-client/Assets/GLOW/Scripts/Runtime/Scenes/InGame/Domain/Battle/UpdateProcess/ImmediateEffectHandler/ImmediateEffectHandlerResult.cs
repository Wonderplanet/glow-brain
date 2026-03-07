using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess.ImmediateEffectHandler
{
    public record ImmediateEffectHandlerResult(
        IReadOnlyList<DeckUnitModel> UpdatedPlayerDeckUnits,
        IReadOnlyList<DeckUnitModel> UpdatedPvpOpponentDeckUnits,
        IReadOnlyList<CharacterUnitModel> UpdatedCharacterUnits,
        AppliedDeckStateEffectResultModel AppliedDeckResult,
        AppliedCharacterImmediateEffectResultModel AppliedCharacterResult)
    {
        public static ImmediateEffectHandlerResult CreateEmpty(
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<CharacterUnitModel> characterUnits)
        {
            return new ImmediateEffectHandlerResult(
                playerDeckUnits,
                pvpOpponentDeckUnits,
                characterUnits,
                AppliedDeckStateEffectResultModel.Empty,
                AppliedCharacterImmediateEffectResultModel.Empty);
        }
    }
}

