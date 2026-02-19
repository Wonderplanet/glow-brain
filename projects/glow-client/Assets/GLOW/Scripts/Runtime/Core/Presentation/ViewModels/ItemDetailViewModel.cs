using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Presentation.ViewModels
{
    public record ItemDetailViewModel(
        ItemName Name,
        ItemDescription Description,
        ItemIconAssetPath ItemIconAssetPath,
        Rarity Rarity,
        ItemAmount Amount);
}
