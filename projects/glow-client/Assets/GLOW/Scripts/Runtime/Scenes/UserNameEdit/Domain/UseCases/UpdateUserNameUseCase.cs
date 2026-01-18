using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Domain.UseCases
{
    public class UpdateUserNameUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public async UniTask UpdateUserName(CancellationToken cancellationToken, string newUserName)
        {
            var userName = new UserName(newUserName);
            await UserService.ChangeName(cancellationToken, userName);

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var now = TimeProvider.Now;
            
            var newGameFetchOther = gameFetchOther with
            {
                UserProfileModel = gameFetchOther.UserProfileModel with 
                {
                    Name = userName,
                    NameUpdateAt = now
                },
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }
    }
}
