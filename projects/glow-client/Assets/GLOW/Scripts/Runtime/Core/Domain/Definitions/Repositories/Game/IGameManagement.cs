using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IGameManagement
    {
        void SaveGameVersion(GameVersionModel gameVersionModel);
        void SaveGameUpdateAndFetch(GameFetchModel gameFetchModel, GameFetchOtherModel gameFetchOtherModel);
        void SaveGameFetch(GameFetchModel gameFetchModel);
        void SaveGameFetchOther(GameFetchOtherModel gameFetchOtherModel);
    }
}
