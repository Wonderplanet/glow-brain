using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases
{
    public class InitializeEncyclopediaArtworkCacheUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }

        public void InitializeOutpostArtworkCache(IReadOnlyList<MasterDataId> mstArtworkId)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userOutpost = gameFetchOther.UserOutpostModels.First(outpost => outpost.IsUsed);

            OutpostArtworkCacheRepository.SetArtworkList(mstArtworkId, userOutpost.MstArtworkId);
        }
    }
}
