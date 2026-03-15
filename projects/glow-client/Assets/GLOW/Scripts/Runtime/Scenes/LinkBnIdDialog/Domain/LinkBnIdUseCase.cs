using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Title.Domains.Definition.Service;
using Zenject;

namespace GLOW.Scenes.LinkBnIdDialog.Domain
{
    public class LinkBnIdUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask LinkBnId(CancellationToken cancellationToken, BnIdCode code)
        {
            var result = await UserService.LinkBnId(cancellationToken, code, true);

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var newGameFetchOther = gameFetchOther with
            {
                BnIdLinkedAt = result.BnIdLinkedAt
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }
    }
}
