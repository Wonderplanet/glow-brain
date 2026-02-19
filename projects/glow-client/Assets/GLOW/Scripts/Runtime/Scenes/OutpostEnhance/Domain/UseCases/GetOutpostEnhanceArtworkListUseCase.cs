using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.OutpostEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class GetOutpostEnhanceArtworkListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IOutpostArtworkCacheRepository OutpostArtworkCacheRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }

        public OutpostEnhanceArtworkListModel GetArtworkListModel()
        {
            var mstArtworks = MstArtworkDataRepository.GetArtworks();
            var displayedOutpostArtworkIds = OutpostArtworkCacheRepository.GetDisplayedArtworkList();

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userOutpost = gameFetchOther.UserOutpostModels.Find(outpost => outpost.IsUsed);
            var userArtworkModels = gameFetchOther.UserArtworkModels;
            var userArtworkFragmentModels = gameFetchOther.UserArtworkFragmentModels;
            var selectedMstArtworkId = OutpostArtworkCacheRepository.GetSelectedArtwork();

            var list = mstArtworks
                .Select(mst => TranslateListCell(mst, displayedOutpostArtworkIds, userArtworkModels, userArtworkFragmentModels, selectedMstArtworkId))
                .OrderByDescending(model => userOutpost.MstArtworkId == model.MstArtworkId)
                .ThenByDescending(model => model.Badge.Value)
                .ThenBy(model => model.IsLock)
                .ToList();

            return new OutpostEnhanceArtworkListModel(list);
        }

        OutpostEnhanceArtworkListCellModel TranslateListCell(
            MstArtworkModel mst,
            IReadOnlyList<MasterDataId> displayedOutpostArtworkIds,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            MasterDataId selectedMstArtworkId
            )
        {
            var isLock = userArtworkModels.All(artwork => artwork.MstArtworkId != mst.Id);
            var isSelect = selectedMstArtworkId == mst.Id;
            var isNew = !isLock && displayedOutpostArtworkIds.All(id => id != mst.Id);

            var mstArtworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(mst, userArtworkModels, userArtworkFragmentModels, isSmall: true);

            return new OutpostEnhanceArtworkListCellModel(
                mst.Id,
                mstArtworkPanelModel,
                new NotificationBadge(isNew),
                isLock,
                isSelect
                );
        }
    }
}
