using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class ArtworkEffectInitializer : IArtworkEffectInitializer
    {
        [Inject] IArtworkEffectModelFactory ArtworkEffectModelFactory { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }

        public ArtworkEffectInitializerResult Initialize(InGameType inGameType)
        {
            // 宣言と初期化
            ArtworkEffectModel artworkEffectModel = ArtworkEffectModel.Empty;
            ArtworkEffectModel pvpOpponentArtworkEffectModel = ArtworkEffectModel.Empty;

            artworkEffectModel = ArtworkEffectModelFactory.Create();

            if (inGameType == InGameType.Pvp)
            {
                var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
                pvpOpponentArtworkEffectModel =  opponentStatus.IsEmpty()
                    ? ArtworkEffectModel.Empty
                    : ArtworkEffectModelFactory.CreatePvpOpponent(opponentStatus.ArtworkPartyStatuses);
            }

            return new ArtworkEffectInitializerResult(
                artworkEffectModel,
                pvpOpponentArtworkEffectModel);
        }
    }
}
