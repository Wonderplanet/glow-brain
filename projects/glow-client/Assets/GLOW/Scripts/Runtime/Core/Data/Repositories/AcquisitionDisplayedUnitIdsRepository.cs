using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Repositories
{
    public class AcquisitionDisplayedUnitIdsRepository : IAcquisitionDisplayedUnitIdsRepository
    {
        IReadOnlyList<MasterDataId> _acquisitionDisplayedUnitIds = new List<MasterDataId>();
        
        void IAcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(IReadOnlyList<MasterDataId> unitIds)
        {
            _acquisitionDisplayedUnitIds = unitIds;
        }

        IReadOnlyList<MasterDataId> IAcquisitionDisplayedUnitIdsRepository.GetAcquisitionDisplayedUnitIds()
        {
            return _acquisitionDisplayedUnitIds;
        }
    }
}