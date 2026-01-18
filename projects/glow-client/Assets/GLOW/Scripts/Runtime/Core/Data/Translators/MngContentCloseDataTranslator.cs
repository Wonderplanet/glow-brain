using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;


namespace GLOW.Core.Data.Translators
{
    public class MngContentCloseDataTranslator
    {

        public static  MngContentCloseModel ToMngContentCloseModel(MngContentCloseData data)
        {
            return new MngContentCloseModel(
                new MasterDataId(data.Id),
                data.ContentType,
                string.IsNullOrEmpty(data.ContentId) ? MasterDataId.Empty : new MasterDataId(data.ContentId),
                data.StartAt,
                data.EndAt);
        }
    }
}
