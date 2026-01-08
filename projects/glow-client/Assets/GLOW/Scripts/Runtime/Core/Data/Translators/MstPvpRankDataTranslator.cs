using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pvp;

namespace GLOW.Core.Data.Translators
{
    public class MstPvpRankDataTranslator
    {
        public static MstPvpRankModel ToMstPvpRankModel(MstPvpRankData data)
        {
            return new MstPvpRankModel(
                new MasterDataId(data.Id),
                new PvpRankAssetKey(data.AssetKey),
                data.RankClassType,
                new PvpRankLevel(data.RankClassLevel),
                new PvpPoint(data.RequiredLowerScore),
                new PvpPoint(data.WinAddPoint),
                new PvpPoint(data.LoseSubPoint));
        }
    }
}