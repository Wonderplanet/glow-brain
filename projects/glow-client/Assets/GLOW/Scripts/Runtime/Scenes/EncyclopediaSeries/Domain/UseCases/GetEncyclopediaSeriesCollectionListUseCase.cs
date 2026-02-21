using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.EncyclopediaSeries.Domain.Models;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.UseCases
{
    public class GetEncyclopediaSeriesCollectionListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }

        public EncyclopediaSeriesCollectionListModel GetCollectionList(MasterDataId mstSeriesId)
        {
            var mstArtworks = MstArtworkDataRepository.GetSeriesArtwork(mstSeriesId);

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var useOutpost = gameFetchOther.UserOutpostModels.Find(outpost => outpost.IsUsed);
            var artworkCellModels = mstArtworks
                .Select(mstArtwork => TranslateArtworkListCellModel(
                    mstArtwork,
                    useOutpost,
                    gameFetchOther.UserArtworkModels,
                    gameFetchOther.UserArtworkFragmentModels))
                .OrderByDescending(model => model.IsUsing)
                .ToList();

            var emblemCellModels = MstEmblemRepository.GetSeriesEmblems(mstSeriesId)
                .Select(model => TranslateEmblemListCellModel(
                    model,
                    gameFetchOther.UserEmblemModel))
                .OrderByDescending(model => model.IsUnlocked)
                .ToList();

            return new EncyclopediaSeriesCollectionListModel(artworkCellModels, emblemCellModels);
        }

        EncyclopediaArtworkListCellModel TranslateArtworkListCellModel(
            MstArtworkModel mst,
            UserHomeOutpostModel userOutpost,
            IReadOnlyList<UserArtworkModel> userArtworks,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragments)
        {
            var isUsing = userOutpost.MstArtworkId == mst.Id;
            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(mst, userArtworks, userArtworkFragments, isSmall:true);
            var userArtwork =
                userArtworks.FirstOrDefault(artwork => artwork.MstArtworkId == mst.Id, UserArtworkModel.Empty);
            var isUnlocked = !userArtwork.IsEmpty();
            var isNew = new NotificationBadge(userArtwork.IsNewEncyclopedia);

            return new EncyclopediaArtworkListCellModel(
                mst.Id,
                artworkPanelModel,
                new EncyclopediaUnlockFlag(isUnlocked),
                new EncyclopediaUnlockFlag(isUsing),
                isNew);
        }

        EncyclopediaEmblemListCellModel TranslateEmblemListCellModel(
            MstEmblemModel mst,
            IReadOnlyList<UserEmblemModel> userEmblemModels)
        {
            var userEmblem = userEmblemModels
                .FirstOrDefault(emblem => emblem.MstEmblemId == mst.Id, UserEmblemModel.Empty);
            var isUnlocked = !userEmblem.IsEmpty();
            var isNew = new NotificationBadge(userEmblem.IsNewEncyclopedia);

            return new EncyclopediaEmblemListCellModel(
                mst.Id,
                EmblemIconAssetPath.FromAssetKey(mst.AssetKey),
                new EncyclopediaUnlockFlag(isUnlocked),
                isNew
            );
        }
    }
}
