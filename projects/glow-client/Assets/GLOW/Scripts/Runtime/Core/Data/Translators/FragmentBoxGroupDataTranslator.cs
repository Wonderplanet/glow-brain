using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class FragmentBoxGroupDataTranslator
    {
        public static MstFragmentBoxGroupModel ToFragmentBoxGroupModel(MstFragmentBoxGroupData data)
        {
            return new MstFragmentBoxGroupModel(
                new MasterDataId(data.MstFragmentBoxGroupId),
                new MasterDataId(data.MstItemId));
        }
    }
}
