using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IAcquisitionDisplayedUnitIdsRepository
    {
        void SetAcquisitionDisplayedUnitIds(IReadOnlyList<MasterDataId> unitIds);
        
        IReadOnlyList<MasterDataId> GetAcquisitionDisplayedUnitIds();
    }
}