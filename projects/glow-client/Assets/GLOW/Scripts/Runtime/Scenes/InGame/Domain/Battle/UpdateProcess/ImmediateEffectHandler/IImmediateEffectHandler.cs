using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess.ImmediateEffectHandler
{
    public interface IImmediateEffectHandler
    {
        ImmediateEffectHandlerResult Handle(
            IAttackResultModel attackResult,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits);
    }
}

