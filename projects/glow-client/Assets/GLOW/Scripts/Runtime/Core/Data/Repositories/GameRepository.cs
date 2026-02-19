using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Core.Data.Repositories
{

    public class GameRepository : IGameRepository, IGameManagement
    {
        [Inject] ISystemInfoProvider _systemInfoProvider;

        GameVersionModel _gameVersionModel;
        GameFetchModel _gameFetchModel;
        GameFetchOtherModel _gameFetchOtherModel;

        void IGameManagement.SaveGameVersion(GameVersionModel gameVersionModel)
        {
            _gameVersionModel = gameVersionModel;
        }

        void IGameManagement.SaveGameUpdateAndFetch(GameFetchModel gameFetchModel, GameFetchOtherModel gameFetchOtherModel)
        {
            _gameFetchModel = gameFetchModel;
            _gameFetchOtherModel = gameFetchOtherModel;
        }

        void IGameManagement.SaveGameFetch(GameFetchModel gameFetchModel)
        {
            _gameFetchModel = gameFetchModel;
        }

        void IGameManagement.SaveGameFetchOther(GameFetchOtherModel gameFetchOtherModel)
        {
            _gameFetchOtherModel = gameFetchOtherModel;
        }

        GameVersionModel IGameRepository.GetGameVersion()
        {
            return _gameVersionModel;
        }

        GameFetchModel IGameRepository.GetGameFetch()
        {
            return _gameFetchModel;
        }

        GameFetchOtherModel IGameRepository.GetGameFetchOther()
        {
            return _gameFetchOtherModel;
        }
    }
}
