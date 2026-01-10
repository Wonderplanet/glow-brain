using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserReceivedUnitEncyclopediaRewardDataTranslator
    {
        public static UserReceivedUnitEncyclopediaRewardModel TranslateToModel(UsrReceivedUnitEncyclopediaRewardData userUnitEncyclopediaRewardData)
        {
            return new UserReceivedUnitEncyclopediaRewardModel(
                new MasterDataId(userUnitEncyclopediaRewardData.MstUnitEncyclopediaRewardId)
                );
        }
    }
}
