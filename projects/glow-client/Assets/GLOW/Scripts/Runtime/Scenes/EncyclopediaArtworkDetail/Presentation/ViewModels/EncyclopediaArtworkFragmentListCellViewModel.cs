using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels
{
    public record EncyclopediaArtworkFragmentListCellViewModel(
        MasterDataId MstArtworkFragmentId,
        QuestType DropQuestType,
        ArtworkFragmentAssetPath AssetPath,
        ArtworkFragmentPositionNum Num,
        ArtworkFragmentName FragmentName,
        Rarity FragmentRarity,
        ArtworkFragmentConditionText DropConditionText,
        ArtworkFragmentStatusFlags StatusFlags
        );
}
