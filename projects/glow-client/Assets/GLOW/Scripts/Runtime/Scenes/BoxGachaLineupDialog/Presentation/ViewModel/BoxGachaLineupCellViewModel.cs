using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.ViewModel
{
    public record BoxGachaLineupCellViewModel(
        PlayerResourceIconViewModel PrizeIconViewModel,
        PlayerResourceName PrizeName,
        BoxGachaPrizeStock PrizeStock)
    {
        public static BoxGachaLineupCellViewModel Empty { get; } = new BoxGachaLineupCellViewModel(
            PlayerResourceIconViewModel.Empty,
            PlayerResourceName.Empty,
            BoxGachaPrizeStock.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

}