using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.ArtworkList.Domain.Models;

namespace GLOW.Scenes.ArtworkList.Presentation.Translators
{
    public static class ArtworkListViewModelTranslator
    {
        public static ArtworkFormationListViewModel TranslateToFormationListViewModel(
            ArtworkListUseCaseModel useCaseModel)
        {
            var cellViewModels = useCaseModel.ArtworkList
                .Select(TranslateToFormationListCellViewModel)
                .ToList();

            return new ArtworkFormationListViewModel(cellViewModels, useCaseModel.SortFilterCategoryModel);
        }

        static ArtworkFormationListCellViewModel TranslateToFormationListCellViewModel(
            ArtworkListCellUseCaseModel cellUseCaseModel)
        {
            var artworkFragmentPanelViewModel = ArtworkPanelViewModelTranslator.ToArtworkFragmentPanelViewModel(
                cellUseCaseModel.ArtworkPanelModel);

            return new ArtworkFormationListCellViewModel(
                cellUseCaseModel.MstArtworkId,
                new ArtworkAssetPath(cellUseCaseModel.MstArtworkId.Value),
                AssignedFlag.Unassigned, // ArtworkListでは常に未編成
                cellUseCaseModel.Rarity,
                cellUseCaseModel.Grade,
                cellUseCaseModel.IsCompleted,
                artworkFragmentPanelViewModel,
                ArtworkGrayOutFlag.False); // ArtworkListではグレーアウトしない
        }
    }
}

