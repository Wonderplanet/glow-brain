using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class ArtworkBonusHpInitializer : IArtworkBonusHpInitializer
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }

        public ArtworkBonusHpInitializationResult Initialize()
        {
            var playerHp = InitializePlayerHp();
            var pvpOpponentHp = InitializePvpOpponentHp();

            return new ArtworkBonusHpInitializationResult(playerHp, pvpOpponentHp);
        }

        HP InitializePlayerHp()
        {
            var userArtworkModels = GameRepository.GetGameFetchOther().UserArtworkModels;
            var mstArtworks = userArtworkModels
                .Join(
                    MstArtworkDataRepository.GetArtworks(),
                    user => user.MstArtworkId,
                    mst => mst.Id,
                    (user, mst) => mst
                );

            return new HP(mstArtworks.Sum(mst => mst.OutpostAdditionalHp.Value));
        }

        HP InitializePvpOpponentHp()
        {
            var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
            var mstArtworks = opponentStatus.MstArtworkIds
                .Join(
                    MstArtworkDataRepository.GetArtworks(),
                    id => id,
                    mst => mst.Id,
                    (id, mst) => mst
                );

            return new HP(mstArtworks.Sum(mst => mst.OutpostAdditionalHp.Value));
        }
    }
}
