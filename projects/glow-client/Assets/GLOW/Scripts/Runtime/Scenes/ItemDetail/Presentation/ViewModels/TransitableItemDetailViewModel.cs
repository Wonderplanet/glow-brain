using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.ItemDetail.Presentation.ViewModels
{
    // TODO: あとで直す
    // ItemDetailViewModel加味したVM作成
    public record TransitableItemDetailViewModel(
        ResourceType Type,
        PlayerResourceName Name,
        PlayerResourceDescription Description,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        ItemDetailAdditionalInformationViewModel AdditionalInformationModel,
        bool IsHideCurrentAmount
    );
}
