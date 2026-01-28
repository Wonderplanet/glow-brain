using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record SelectableLineupFragmentViewModel(
        MasterDataId ItemId,
        ItemIconAssetPath ItemIconAssetPath,
        Rarity Rarity,
        ItemName Name,
        bool IsSelected)
    {
        public static SelectableLineupFragmentViewModel Empty { get; } = new (
            MasterDataId.Empty,
            ItemIconAssetPath.Empty,
            Rarity.R,
            ItemName.Empty,
            false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
