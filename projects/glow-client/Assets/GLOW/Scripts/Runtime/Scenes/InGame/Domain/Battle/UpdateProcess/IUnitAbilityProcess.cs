using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IUnitAbilityProcess
    {
        IReadOnlyList<CharacterUnitModel> UpdateUnitAbility(IReadOnlyList<CharacterUnitModel> characterUnits);
    }
}
