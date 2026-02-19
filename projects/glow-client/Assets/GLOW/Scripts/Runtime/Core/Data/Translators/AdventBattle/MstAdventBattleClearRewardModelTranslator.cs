using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators.AdventBattle
{
    public class MstAdventBattleClearRewardModelTranslator
    {
        public static MstAdventBattleClearRewardModel ToMstAdventBattleClearRewardModel(MstAdventBattleClearRewardData data)
        {
            return new MstAdventBattleClearRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstAdventBattleId),
                data.RewardCategory,
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount),
                new Percentage(data.Percentage),
                new SortOrder(data.SortOrder));
        }
    }
}
