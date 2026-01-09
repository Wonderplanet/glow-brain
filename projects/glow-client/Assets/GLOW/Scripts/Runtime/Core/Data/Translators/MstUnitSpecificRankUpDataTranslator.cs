using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstUnitSpecificRankUpDataTranslator
    {
        public static MstUnitSpecificRankUpModel Translate(MstUnitSpecificRankUpData data)
        {
            return new MstUnitSpecificRankUpModel(
                new MasterDataId(data.MstUnitId),
                new UnitRank(data.Rank),
                new ItemAmount(data.Amount),
                new ItemAmount(data.UnitMemoryAmount),
                new UnitLevel(data.RequireLevel),
                new ItemAmount(data.SrMemoryFragmentAmount),
                new ItemAmount(data.SsrMemoryFragmentAmount),
                new ItemAmount(data.UrMemoryFragmentAmount)
            );
        }
    }
}
