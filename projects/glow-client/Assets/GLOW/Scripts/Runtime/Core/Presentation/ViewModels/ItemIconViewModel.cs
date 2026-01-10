using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.ViewModels
{
    public record ItemIconViewModel(
        MasterDataId ItemId,
        ItemIconAssetPath ItemIconAssetPath,
        Rarity Rarity,
        ItemAmount Amount)
    {
        public static ItemIconViewModel Empty { get; } =  new ItemIconViewModel(
            MasterDataId.Empty,
            ItemIconAssetPath.Empty,
            Rarity.R,
            ItemAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

}
