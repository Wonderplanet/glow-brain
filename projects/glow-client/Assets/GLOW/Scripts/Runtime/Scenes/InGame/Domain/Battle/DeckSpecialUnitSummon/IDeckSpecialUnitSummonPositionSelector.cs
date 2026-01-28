using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IDeckSpecialUnitSummonPositionSelector
    {
        PageCoordV2 SelectSummonPosition(
            PageCoordV2 pos,
            AttackData attackData,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide);
    }
}
