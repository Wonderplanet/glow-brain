using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEventDisplayRewardDataTranslator
    {
        public static MstEventDisplayRewardModel Translate(MstEventDisplayRewardData data)
        {
            return new MstEventDisplayRewardModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstEventId),
                data.ResourceType,
                new MasterDataId(data.ResourceId),
                new SortOrder(data.SortOrder)
            );
        }
    }
}