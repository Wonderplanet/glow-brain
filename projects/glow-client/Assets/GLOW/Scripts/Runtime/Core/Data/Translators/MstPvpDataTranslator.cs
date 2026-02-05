using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public static class MstPvpDataTranslator
    {
        public static MstPvpModel TranslateMstPvpModel(
            MstPvpData data,
            MstPvpI18nData i18NData)
        {
            return new MstPvpModel(
                new MasterDataId(data.Id),
                data.RankingMinPvpRankClass,
                new PvpDailyChallengeCount(data.MaxDailyChallengeCount),
                new PvpDailyChallengeCount(data.MaxDailyItemChallengeCount),
                new PvpItemChallengeCost(data.ItemChallengeCostAmount),
                new BattlePoint(data.InitialBattlePoint),
                new PvpName(i18NData.Name),
                new PvpDescription(i18NData.Description));
        }

        public static MstPvpRankModel ToMstPvpRankModel(MstPvpRankData data)
        {
            return new MstPvpRankModel(
                new MasterDataId(data.Id),
                new PvpRankAssetKey(data.AssetKey),
                data.RankClassType,
                new PvpRankLevel(data.RankClassLevel),
                new PvpPoint(data.RequiredLowerScore),
                new PvpPoint(data.WinAddPoint),
                new PvpPoint(data.LoseSubPoint)
            );
        }
    }
}
