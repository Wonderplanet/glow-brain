using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Core.Domain.Helper
{
    public interface IArtworkPanelHelper
    {
        public ArtworkPanelModel CreateArtworkPanelModel(
            MstArtworkModel mstArtwork,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            bool isSmall = false
        );
    }

    public class ArtworkPanelHelper : IArtworkPanelHelper
    {
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }

        public ArtworkPanelModel CreateArtworkPanelModel(
            MstArtworkModel mstArtwork,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            bool isSmall
        )
        {
            var isRelease = userArtworkModels
                .Any(artwork => artwork.MstArtworkId == mstArtwork.Id);
            var mstArtworkFragments = MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtwork.Id);
            var fragmentModels = mstArtworkFragments
                .Select(fragment => ToTranslateFragmentModel(fragment, userArtworkFragmentModels, isRelease))
                .ToList();

            var artworkAssetPath = isSmall
                ? ArtworkAssetPath.CreateSmall(mstArtwork.AssetKey)
                : ArtworkAssetPath.Create(mstArtwork.AssetKey);

            return new ArtworkPanelModel(
                artworkAssetPath,
                fragmentModels);

        }

        ArtworkFragmentModel ToTranslateFragmentModel(
            MstArtworkFragmentModel mstArtworkFragmentModel,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            bool isRelease)
        {
            var isUnlock = isRelease || userArtworkFragmentModels.Any(model =>
                model.MstArtworkFragmentId == mstArtworkFragmentModel.Id
                && model.MstArtworkId == mstArtworkFragmentModel.MstArtworkId);
            return new ArtworkFragmentModel(
                mstArtworkFragmentModel.Position,
                isUnlock
                );
        }
    }
}
