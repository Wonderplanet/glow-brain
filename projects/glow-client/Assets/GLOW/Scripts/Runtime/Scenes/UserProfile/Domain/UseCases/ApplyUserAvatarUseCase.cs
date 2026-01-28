using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserProfile.Domain.UseCases
{
    public class ApplyUserAvatarUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IUserProfileBadgeRepository UserProfileBadgeRepository { get; }
        public async UniTask ApplyUserAvatar(CancellationToken cancellationToken, MasterDataId mstUnitId)
        {
            var result = await UserService.ChangeAvatar(cancellationToken, mstUnitId);
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
                UserProfileModel = result.UserProfileModel
            };

            RegisterDisplayedUserProfileAvatarId(gameFetchOther);

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }

        void RegisterDisplayedUserProfileAvatarId(GameFetchOtherModel gameFetchOtherModel)
        {
            var mstUnitIdList = gameFetchOtherModel.UserUnitModels
                .Select(unit => unit.MstUnitId)
                .ToList();

            UserProfileBadgeRepository.DisplayedUserProfileAvatarIds = mstUnitIdList;
        }
    }
}
