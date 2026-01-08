using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IGameRepository
    {
        GameVersionModel GetGameVersion();
        GameFetchModel GetGameFetch();
        GameFetchOtherModel GetGameFetchOther();
    }
}
