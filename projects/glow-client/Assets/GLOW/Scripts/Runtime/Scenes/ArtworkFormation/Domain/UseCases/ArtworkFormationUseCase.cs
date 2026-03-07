using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFormation.Domain.Models;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.UnitList.Domain.Misc;
using Zenject;

namespace GLOW.Scenes.ArtworkFormation.Domain.UseCases
{
    public class ArtworkFormationUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkEffectRepository MstArtworkEffectRepository { get; }
        [Inject] IArtworkSortFilterCacheRepository ArtworkSortFilterCacheRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IArtworkPanelHelper ArtworkPanelHelper { get; }
        [Inject] IArtworkListFilterAndSort ArtworkListFilterAndSort { get; }

        public ArtworkFormationUseCaseModel GetArtworkFormationUseCaseModel(
            IReadOnlyList<MasterDataId> assignedArtworkIds)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var mstArtworkIds = gameFetchOther.UserArtworkPartyModel.GetArtworkList()
                .Where(id => !id.IsEmpty())
                .ToList();

            // 編成情報が未指定の場合はDBの編成済みIDsを優先ソートに使用する
            var assignedArtworkIdsForSort = assignedArtworkIds.Count > 0 ? assignedArtworkIds : mstArtworkIds;

            return CreateUseCaseModel(mstArtworkIds, assignedArtworkIdsForSort, gameFetchOther);
        }

        ArtworkFormationUseCaseModel CreateUseCaseModel(
            IReadOnlyList<MasterDataId> mstArtworkIds,
            IReadOnlyList<MasterDataId> assignedArtworkIdsForSort,
            GameFetchOtherModel gameFetchOther)
        {
            var mstArtworks = MstArtworkDataRepository.GetArtworks();
            var userArtworkModels = gameFetchOther.UserArtworkModels;
            var userArtworkFragmentModels = gameFetchOther.UserArtworkFragmentModels;

            // userArtworkModelsのMstArtworkIdと一致するmstArtworksのみを抽出する
            mstArtworks = mstArtworks
                .Where(mst => userArtworkModels.Any(user => user.MstArtworkId == mst.Id))
                .ToList();

            // ソートフィルター反映済みの原画リスト一覧情報（編成中の原画を先頭に表示）
            (var artworkList, var filterCategoryModel) = CreateArtworkList(
                mstArtworks,
                userArtworkModels,
                userArtworkFragmentModels,
                assignedArtworkIdsForSort);

            // 全ての原画リスト一覧情報（ソートフィルター未反映）
            var allArtworkList = CreateAllArtworkModels(
                mstArtworks,
                userArtworkModels,
                userArtworkFragmentModels);

            return new ArtworkFormationUseCaseModel(
                mstArtworkIds,
                artworkList,
                allArtworkList,
                filterCategoryModel);
        }

        (List<ArtworkFormationArtworkModel>, ArtworkSortFilterCategoryModel) CreateArtworkList(
            IReadOnlyList<MstArtworkModel> mstArtworks,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels,
            IReadOnlyList<MasterDataId> assignedArtworkIdsForSort)
        {
            var mstArtworkEffectModels = mstArtworks
                .Select(artwork => MstArtworkEffectRepository.GetMstInGameArtworkEffectModelFirstOrDefault(artwork.Id))
                .ToList();

            var filterCategoryModel = ArtworkSortFilterCacheRepository.GetModel(ArtworkSortFilterCacheType.PartyFormation);
            var filterAndSortedMstArtworks = ArtworkListFilterAndSort.FilterAndSort(
                userArtworkModels,
                mstArtworks,
                mstArtworkEffectModels,
                filterCategoryModel,
                MstSeriesDataRepository.GetMstSeriesModels());

            // filterAndSortedMstArtworksのソート順を維持しつつ、編成中の原画を先頭に移動する
            // LINQのOrderByDescendingは安定ソートのため、同じキー値の要素間では元の順序が保持される
            var assignedPrioritizedMstArtworks = filterAndSortedMstArtworks
                .OrderByDescending(mst => assignedArtworkIdsForSort.Contains(mst.Id))
                .ToList();

            var models = assignedPrioritizedMstArtworks
                .Select(mst => CreateArtworkModel(mst, userArtworkModels, userArtworkFragmentModels))
                .ToList();
            return (models, filterCategoryModel);
        }

        List<ArtworkFormationArtworkModel> CreateAllArtworkModels(
            IReadOnlyList<MstArtworkModel> mstArtworks,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            return mstArtworks
                .Select(mst => CreateArtworkModel(mst, userArtworkModels, userArtworkFragmentModels))
                .ToList();
        }

        ArtworkFormationArtworkModel CreateArtworkModel(
            MstArtworkModel mstArtworkModel,
            IReadOnlyList<UserArtworkModel> userArtworkModels,
            IReadOnlyList<UserArtworkFragmentModel> userArtworkFragmentModels)
        {
            var userArtwork = userArtworkModels.Find(artwork => artwork.MstArtworkId == mstArtworkModel.Id);
            var isCompleted = userArtwork != null && !userArtwork.IsEmpty();

            var artworkPanelModel = ArtworkPanelHelper.CreateArtworkPanelModel(
                mstArtworkModel,
                userArtworkModels,
                userArtworkFragmentModels);

            // 完成済みの原画はUserArtworkModelのGradeを使用、未完成の場合はEmpty
            var grade = isCompleted ? userArtwork.Grade : ArtworkGradeLevel.Empty;

            return new ArtworkFormationArtworkModel(
                mstArtworkModel.Id,
                ArtworkAssetPath.Create(mstArtworkModel.AssetKey),
                mstArtworkModel.Rarity,
                new ArtworkCompleteFlag(isCompleted),
                artworkPanelModel,
                grade);
        }
    }
}
