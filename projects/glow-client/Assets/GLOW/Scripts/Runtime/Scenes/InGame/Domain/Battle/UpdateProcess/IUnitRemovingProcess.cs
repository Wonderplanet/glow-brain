using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IUnitRemovingProcess
    {
        UnitRemovingProcessResult Update(
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<SpecialUnitModel> specialUnits);
    }
}
