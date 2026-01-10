using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.AgeConfirm.Domain
{
    public class UserStoreInfoUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public UserStoreInfoModel GetUserStoreInfoModel()
        {
            var fetchOther = GameRepository.GetGameFetchOther();
            return fetchOther.UserStoreInfoModel;
        }
    }
}
