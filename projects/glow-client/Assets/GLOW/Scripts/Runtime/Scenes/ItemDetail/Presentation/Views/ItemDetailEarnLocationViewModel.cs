using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Scenes.ItemDetail.Presentation.Views
{
    public record ItemDetailEarnLocationViewModel(
        ItemTransitionType TransitionType,
        MasterDataId MasterDataId,
        TransitionPossibleFlag TransitionPossibleFlag);
}