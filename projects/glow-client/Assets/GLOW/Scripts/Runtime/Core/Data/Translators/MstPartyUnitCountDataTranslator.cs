using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstPartyUnitCountDataTranslator
    {
        public static MstPartyUnitCountModel ToMstPartyUnitCountModel(MstPartyUnitCountData data)
        {
            return new MstPartyUnitCountModel(
                string.IsNullOrEmpty(data.MstStageId) ? MasterDataId.Empty : new MasterDataId(data.MstStageId),
                new PartyMemberSlotCount(data.MaxCount));
        }
    }
}
