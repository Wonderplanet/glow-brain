using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record MstPvpRankModel(
        MasterDataId Id,
        PvpRankAssetKey AssetKey,
        PvpRankClassType RankClassType,
        PvpRankLevel RankLevel,
        PvpPoint RequiredLowerPoint,
        PvpPoint WinAddPoint,
        PvpPoint LoseSubPoint)
    {
        public static MstPvpRankModel Empty { get; } = new MstPvpRankModel(
            MasterDataId.Empty,
            PvpRankAssetKey.Empty,
            PvpRankClassType.Bronze,
            PvpRankLevel.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty,
            PvpPoint.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}