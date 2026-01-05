using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Debugs.Command.Domains.Models
{
    public record UnitTemporaryParameterModel(
        MasterDataId Id,
        UnitAssetKey AssetKey,
        UnitMoveSpeed MoveSpeed,
        WellDistance WellDistance,
        TickCount AttackDelay);
}
