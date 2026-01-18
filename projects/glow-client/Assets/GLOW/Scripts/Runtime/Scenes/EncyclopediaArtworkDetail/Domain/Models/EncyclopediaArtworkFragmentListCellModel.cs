using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models
{
    public record EncyclopediaArtworkFragmentListCellModel(
        MasterDataId MstArtworkFragmentId,
        QuestType QuestType,
        ArtworkFragmentAssetPath AssetPath,
        ArtworkFragmentPositionNum Num,
        ArtworkFragmentName FragmentName,
        Rarity FragmentRarity,
        ArtworkFragmentConditionText DropConditionText,
        ArtworkFragmentStatusFlags StatusFlags);
}
