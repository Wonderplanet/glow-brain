using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstUnitEncyclopediaRewardDataTranslator
    {
        public static MstUnitEncyclopediaRewardModel ToUnitEncyclopediaRewardModel(MstUnitEncyclopediaRewardData data)
        {
            return new MstUnitEncyclopediaRewardModel(
                new MasterDataId(data.Id),
                new UnitGrade(data.UnitEncyclopediaRank),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new ObscuredPlayerResourceAmount(data.ResourceAmount)
            );
        }
    }
}
