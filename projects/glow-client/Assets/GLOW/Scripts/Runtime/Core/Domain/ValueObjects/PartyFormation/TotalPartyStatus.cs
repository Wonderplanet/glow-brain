using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TotalPartyStatus(decimal Value)
    {
        public static TotalPartyStatus Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static TotalPartyStatus operator +(TotalPartyStatus a, TotalPartyStatus b)
        {
            return new TotalPartyStatus(a.Value + b.Value);
        }

        public static TotalPartyStatus operator +(TotalPartyStatus a, HP b)
        {
            return new TotalPartyStatus(a.Value + b.Value);
        }

        public static TotalPartyStatus operator +(TotalPartyStatus a, AttackPower b)
        {
            return new TotalPartyStatus(a.Value + b.Value);
        }

        public string ToStringSeparated()
        {
            return Value.ToString("N0");
        }
    }
}
