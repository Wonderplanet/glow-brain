using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGacha.Presentation.ViewModel
{
    public record BoxGachaPrizeCellViewModel(
        PickUpFlag IsPickUp,
        PlayerResourceIconViewModel PrizeResourceViewModel,
        GachaDrawCount DrawCount,
        BoxGachaPrizeStock Stock)
    {
        public static BoxGachaPrizeCellViewModel Empty { get; } = new(
            PickUpFlag.False,
            PlayerResourceIconViewModel.Empty,
            GachaDrawCount.Empty,
            BoxGachaPrizeStock.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}