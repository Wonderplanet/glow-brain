using System;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.HomeMenu.Domain.UseCase
{
    public class LinkedBnIdCheckUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        public DateTimeOffset GetLinkedBnIdAt()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            return gameFetchOther.BnIdLinkedAt ?? DateTimeOffset.MinValue;
        }
    }
}
