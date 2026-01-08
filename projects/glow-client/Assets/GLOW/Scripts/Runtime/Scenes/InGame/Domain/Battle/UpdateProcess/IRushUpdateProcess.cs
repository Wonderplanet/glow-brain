using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IRushUpdateProcess
    {
        RushUpdateProcessResult UpdateRush(
            RushModel rushModel,
            RushModel pvpOpponentRushModel,
            TickCount tickCount,
            IReadOnlyList<CharacterUnitModel> fieldUnits,
            IReadOnlyList<MasterDataId> usedSpecialUnitIdsBeforeNextRush,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            MstPageModel mstPage,
            IReadOnlyList<IAttackModel> attacks);
    }
}
