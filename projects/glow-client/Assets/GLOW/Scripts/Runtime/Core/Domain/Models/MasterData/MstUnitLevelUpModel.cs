using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstUnitLevelUpModel(UnitLevel Level, UnitLabel UnitLabel, Coin RequiredCoin)
    {
        public static MstUnitLevelUpModel Empty { get; } = new(
            UnitLevel.Empty,
            UnitLabel.DropR,
            Coin.Empty);
    }
}
