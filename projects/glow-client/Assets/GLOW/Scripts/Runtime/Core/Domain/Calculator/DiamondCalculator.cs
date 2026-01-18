using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Calculator
{
    public class DiamondCalculator
    {
        public static (PaidDiamond paid, FreeDiamond free) CalculateAfterDiamonds(PaidDiamond paidDiamond, FreeDiamond freeDiamond, TotalDiamond consumeDiamond)
        {
            if (consumeDiamond.Value <= freeDiamond.Value)
            {
                //無償から消費
                return (paidDiamond, new FreeDiamond(freeDiamond.Value - consumeDiamond.Value));
            }
            else if (consumeDiamond.Value <= (paidDiamond.Value + freeDiamond.Value))
            {
                //無償使い切り、有償一部
                var leftValue = consumeDiamond.Value - freeDiamond.Value;
                return (new PaidDiamond(paidDiamond.Value - leftValue), new FreeDiamond(0));
            }
            else
            {
                //不足(無償分をマイナス記述する)
                var shortageValue = (paidDiamond.Value + freeDiamond.Value) - consumeDiamond.Value;
                return (new PaidDiamond(0), new FreeDiamond(shortageValue));
            }
        }

        public static PaidDiamond CalculateAfterOnlyPaidDiamond(PaidDiamond paidDiamond, TotalDiamond consumeDiamond)
        {
            return new PaidDiamond(paidDiamond.Value - consumeDiamond.Value);
        }
    }
}
