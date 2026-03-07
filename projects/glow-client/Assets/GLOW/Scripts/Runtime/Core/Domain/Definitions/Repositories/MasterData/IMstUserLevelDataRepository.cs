using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstUserLevelDataRepository
    {
        MstUserLevelModel GetUserLevelModel(UserLevel level);
        MstUserLevelModel GetMaxUserLevelModel();
    }
}
