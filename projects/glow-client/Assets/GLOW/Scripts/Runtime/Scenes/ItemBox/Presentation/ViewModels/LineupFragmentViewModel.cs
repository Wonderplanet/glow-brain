using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record LineupFragmentViewModel(
        MasterDataId ItemId,
        ItemIconAssetPath ItemIconAssetPath,
        Rarity Rarity,
        ItemName Name)
    {
        public static LineupFragmentViewModel Empty { get; } = new (
            MasterDataId.Empty,
            ItemIconAssetPath.Empty,
            Rarity.R,
            ItemName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
