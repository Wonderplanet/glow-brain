using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstArtworkFragmentDataRepository
    {
        IReadOnlyList<MstArtworkFragmentModel> GetArtworkFragmentModels();
        IReadOnlyList<IGrouping<MasterDataId, MstArtworkFragmentModel>> GetArtworkFragmentsGroupByMstDropGroupId();
        MstArtworkFragmentModel GetArtworkFragment(MasterDataId artworkFragmentId);
        IReadOnlyList<MstArtworkFragmentModel> GetArtworkFragments(MasterDataId artworkId);
        IReadOnlyList<MstArtworkFragmentModel> GetDropGroupArtworkFragments(MasterDataId dropGroupId);
    }
}
