using System.Collections.Generic;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Repositories
{
    public interface IOprGachaUpperRepository
    {
        IReadOnlyList<OprDrawCountThresholdModel> FindByDrawCountThresholdGroupId(DrawCountThresholdGroupId drawCountThresholdGroupId);
    }
}
