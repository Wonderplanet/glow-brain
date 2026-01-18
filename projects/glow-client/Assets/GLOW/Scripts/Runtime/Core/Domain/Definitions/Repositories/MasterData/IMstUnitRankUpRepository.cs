using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstUnitRankUpRepository
    {
        IReadOnlyList<MstUnitRankUpModel> GetUnitRankUpList(UnitLabel unitLabel);
    }
}
