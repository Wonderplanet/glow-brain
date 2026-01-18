using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IImmediateEffectHandler
    {
        (IReadOnlyList<DeckUnitModel> updatedPlayerDeckUnits,
            IReadOnlyList<DeckUnitModel> updatedPvpOpponentDeckUnits,
            IReadOnlyList<AppliedDeckStateEffectResultModel> appliedResults) Handle(
            IAttackResultModel attackResult,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits);
    }
}

