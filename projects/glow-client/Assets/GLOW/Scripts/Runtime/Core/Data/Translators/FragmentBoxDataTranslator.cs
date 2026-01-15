using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class FragmentBoxDataTranslator
    {
        public static MstFragmentBoxModel ToFragmentBoxModel(MstFragmentBoxData data)
        {
            return new MstFragmentBoxModel(
                new MasterDataId(data.MstItemId),
                new MasterDataId(data.MstFragmentBoxGroupId));
        }
    }
}
