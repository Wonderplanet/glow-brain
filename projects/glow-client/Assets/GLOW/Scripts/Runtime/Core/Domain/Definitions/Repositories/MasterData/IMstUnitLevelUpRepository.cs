using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstUnitLevelUpRepository
    {
        IReadOnlyList<MstUnitLevelUpModel> GetUnitLevelUpList();
        IReadOnlyList<MstUnitLevelUpModel> GetUnitLevelUpList(UnitLabel unitLabel);
        MstUnitLevelUpModel GetUnitMaxLevelUp(UnitLabel unitLabel);
    }
}
