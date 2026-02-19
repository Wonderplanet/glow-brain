using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class EarnLocationDataTranslator
    {
        public static MstItemTransitionModel ToEarnLocationModel(MstItemTransitionData data)
        {
            return new MstItemTransitionModel(
                new MasterDataId(data.MstItemId),
                data.Transition1,
                string.IsNullOrEmpty(data.Transition1MstId) ? MasterDataId.Empty : new MasterDataId(data.Transition1MstId),
                data.Transition2,
                string.IsNullOrEmpty(data.Transition2MstId) ? MasterDataId.Empty : new MasterDataId(data.Transition2MstId)
            );
        }
    }
}
