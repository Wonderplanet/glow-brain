using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ArtworkExpandDialog.Domain.Evaluator
{
    public class HasArtworkEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }

        public bool HasArtwork(MasterDataId mstArtworkId)
        {
            var userArtworks = GameRepository.GetGameFetchOther().UserArtworkModels
                .FirstOrDefault(a => a.MstArtworkId == mstArtworkId, UserArtworkModel.Empty);

            return !userArtworks.IsEmpty();
        }
    }
}
