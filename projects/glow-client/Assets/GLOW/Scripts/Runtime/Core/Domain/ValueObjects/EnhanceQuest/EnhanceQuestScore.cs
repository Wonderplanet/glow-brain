using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record EnhanceQuestScore(ObscuredLong Value)
    {
        public static EnhanceQuestScore Empty { get; } = new (0);
        public static EnhanceQuestScore Zero { get; } = new (0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }
    }
}
