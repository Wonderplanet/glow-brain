using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.PackShop.Presentation.ViewModels
{
    public record PackShopProductItemViewModel(PlayerResourceIconViewModel ResourceIcon, MasterDataId Id);
}
