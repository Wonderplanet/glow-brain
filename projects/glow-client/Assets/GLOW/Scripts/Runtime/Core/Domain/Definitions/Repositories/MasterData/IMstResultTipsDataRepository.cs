using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IMstResultTipsDataRepository
    {
        MstResultTipsModel GetMstResultTipsFirstOrDefault(UserLevel userLevel);
    }
}
