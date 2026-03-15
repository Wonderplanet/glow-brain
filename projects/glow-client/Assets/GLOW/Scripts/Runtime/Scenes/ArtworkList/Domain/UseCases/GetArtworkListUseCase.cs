using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkList.Domain.Models;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.UnitList.Domain.Misc;
using Zenject;

namespace GLOW.Scenes.ArtworkList.Domain.UseCases
{
    public class GetArtworkListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkEffectRepository MstArtworkEffectRepository { get; }
        [Inject] IArtworkSortFilterCacheRepository ArtworkSortFilterCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }
        [Inject] IArtworkListFilterAndSort ArtworkListFilterAndSort { get; }

        public ArtworkListUseCaseModel GetArtworkListUseCaseModel()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userArtworkModels = gameFetchOther.UserArtworkModels;
            var userArtworkFragmentModels = gameFetchOther.UserArtworkFragmentModels;
            var mstArtworkModels = MstArtworkDataRepository.GetArtworks();
            var mstArtworkEffectModels = mstArtworkModels
                .Select(artwork => MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(artwork.Id))
                .ToList();

            // userArtworkModelsのMstArtworkIdと一致するmstArtworksのみを抽出する
            mstArtworkModels = mstArtworkModels
                .Where(mst => userArtworkModels.Any(user => user.MstArtworkId == mst.Id))
                .ToList();

            var filterCategoryModel = ArtworkSortFilterCacheRepository.GetModel(ArtworkSortFilterCacheType.ArtworkList);
            mstArtworkModels = ArtworkListFilterAndSort.FilterAndSort(
                userArtworkModels,
                mstArtworkModels,
                mstArtworkEffectModels,
                filterCategoryModel,
                MstSeriesDataRepository.GetMstSeriesModels());

            var artworkList = CreateArtworkList(userArtworkModels, userArtworkFragmentModels, mstArtworkModels);

            return new ArtworkListUseCaseModel(artworkList, filterCategoryModel);
        }

        IReadOnlyList<ArtworkListCellUseCaseModel> CreateArtworkList(
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            IReadOnlyList<MstArtworkModel> mstArtworkModels)
        {
            return mstArtworkModels
                .Select(mstArtworkModel => CreateArtworkListCell(
                    mstArtworkModel,
                    userArtworkModels,
                    userArtworkFragmentModels))
                .ToList();
        }

        ArtworkListCellUseCaseModel CreateArtworkListCell(
            MstArtworkModel mstArtworkModel,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var userArtwork = userArtworkModels.FirstOrDefault(
                artwork => artwork.MstArtworkId == mstArtworkModel.Id,
                UserArtworkModel.Empty);

            var isCompleted = !userArtwork.IsEmpty();
            var grade = isCompleted ? userArtwork.Grade : ArtworkGradeLevel.Empty;

            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(
                mstArtworkModel,
                userArtworkModels,
                userArtworkFragmentModels);

            return new ArtworkListCellUseCaseModel(
                mstArtworkModel.Id,
                new ArtworkCompleteFlag(isCompleted),
                artworkPanelModel,
                mstArtworkModel.Rarity,
                grade);
        }
    }
}

