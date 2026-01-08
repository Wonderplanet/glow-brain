using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ItemBox.Presentation.ViewModels
{
    public record RandomFragmentBoxViewModel(
        MasterDataId MstItemId,
        ItemDetailViewModel ItemDetail,
        ItemAmount LimitUseAmount);
}
