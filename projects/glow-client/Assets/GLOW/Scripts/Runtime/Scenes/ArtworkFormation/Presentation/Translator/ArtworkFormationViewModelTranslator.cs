using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFormation.Domain.Models;
using GLOW.Scenes.ArtworkFormation.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.UnitList.Domain.Misc;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Translator
{
    public static class ArtworkFormationViewModelTranslator
    {
        public static ArtworkFormationViewModel TranslateToViewModel(ArtworkFormationUseCaseModel useCaseModel)
        {
            var assignedArtworkIds = useCaseModel.AssignedFormationArtworkIds;
            var artworkListModels = useCaseModel.ArtworkListModels;
            var allArtworkListModels = useCaseModel.AllArtworkModels;

            return new ArtworkFormationViewModel(
                TranslateToPartyViewModel(assignedArtworkIds, allArtworkListModels),
                TranslateToListViewModel(assignedArtworkIds, artworkListModels, useCaseModel.SortFilterCategoryModel));
        }

        public static ArtworkFormationPartyViewModel TranslateToPartyViewModel(
            IReadOnlyList<MasterDataId> assignedArtworkIds,
            IReadOnlyList<ArtworkFormationArtworkModel> artworkListModels)
        {
            var cellViewModels = assignedArtworkIds
                .Select(id => FindArtworkModel(artworkListModels, id))
                .Select(TranslateToPartyCellViewModel)
                .ToList();

            return new ArtworkFormationPartyViewModel(cellViewModels);
        }

        static ArtworkFormationPartyCellViewModel TranslateToPartyCellViewModel(ArtworkFormationArtworkModel artworkModel)
        {
            if(artworkModel.IsEmpty())
            {
                return ArtworkFormationPartyCellViewModel.Empty;
            }

            return new ArtworkFormationPartyCellViewModel(
                artworkModel.MstArtworkId,
                artworkModel.AssetPath,
                artworkModel.Rarity,
                artworkModel.Grade);
        }

        static ArtworkFormationListViewModel TranslateToListViewModel(
            IReadOnlyList<MasterDataId> assignedArtworkIds,
            IReadOnlyList<ArtworkFormationArtworkModel> artworkListModels,
            ArtworkSortFilterCategoryModel sortFilterCategoryModel)
        {
            var cellViewModels = artworkListModels
                .Select(model => TranslateToListCellViewModel(model, assignedArtworkIds))
                .ToList();

            return new ArtworkFormationListViewModel(cellViewModels, sortFilterCategoryModel);
        }

        static ArtworkFormationListCellViewModel TranslateToListCellViewModel(
            ArtworkFormationArtworkModel artworkModel,
            IReadOnlyList<MasterDataId> assignedArtworkIds)
        {
            var isAssigned = assignedArtworkIds.Contains(artworkModel.MstArtworkId)
                ? AssignedFlag.Assigned
                : AssignedFlag.Unassigned;

            var artworkFragmentPanelViewModel =
                ArtworkPanelViewModelTranslator.ToArtworkFragmentPanelViewModel(artworkModel.ArtworkPanelModel);

            // 編成が10枚で未編成の場合はグレーアウト
            var isGrayOut = !isAssigned && assignedArtworkIds.Count >= 10
                ? ArtworkGrayOutFlag.True
                : ArtworkGrayOutFlag.False;

            return new ArtworkFormationListCellViewModel(
                artworkModel.MstArtworkId,
                artworkModel.AssetPath,
                isAssigned,
                artworkModel.Rarity,
                artworkModel.Grade,
                artworkModel.IsCompleted,
                artworkFragmentPanelViewModel,
                isGrayOut);
        }

        static ArtworkFormationArtworkModel FindArtworkModel(
            IReadOnlyList<ArtworkFormationArtworkModel> artworkListModels,
            MasterDataId mstArtworkId)
        {
            return artworkListModels.FirstOrDefault(
                model => model.MstArtworkId == mstArtworkId,
                ArtworkFormationArtworkModel.Empty);
        }
    }
}
