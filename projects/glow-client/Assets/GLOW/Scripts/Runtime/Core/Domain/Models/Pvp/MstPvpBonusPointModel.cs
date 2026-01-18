using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpBonusPointModel(
        MasterDataId Id,
        PvpBonusType BonusType,
        PvpBonusPointConditionValue ConditionValue,
        PvpBonusPoint BonusPoint)
    {
        public static MstPvpBonusPointModel Empty { get; } = new MstPvpBonusPointModel(
            MasterDataId.Empty,
            PvpBonusType.ClearTime,
            PvpBonusPointConditionValue.Empty,
            PvpBonusPoint.Empty);
    }
}