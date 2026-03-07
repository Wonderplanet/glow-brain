using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record RatioProbabilityAmount(Rarity Rarity, int Amount)
    {
        public override string ToString()
        {
            return $"{Rarity.ToString()}(全{Amount}種)";
        }
    }
}
