using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface ISpecialUnitUpdateProcess
    {
        SpecialUnitUpdateProcessResult UpdateSpecialUnits(
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<IAttackModel> attacks,
            MstPageModel mstPage,
            TickCount tickCount);
    }
}
