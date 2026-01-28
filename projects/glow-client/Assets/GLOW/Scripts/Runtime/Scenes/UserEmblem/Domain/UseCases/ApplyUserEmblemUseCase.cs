using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Domain.UseCases
{
    public class ApplyUserEmblemUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask ApplyUserEmblem(CancellationToken cancellationToken, MasterDataId mstEmblemId)
        {
            var result = await UserService.ChangeEmblem(cancellationToken, mstEmblemId);
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
                UserProfileModel = result.UserProfileModel
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);

        }
    }
}
