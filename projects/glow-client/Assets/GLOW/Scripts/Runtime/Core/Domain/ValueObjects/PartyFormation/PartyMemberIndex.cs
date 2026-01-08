namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyMemberIndex(int Value)
    {
        public static PartyMemberIndex Empty { get; } = new (-1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public static bool operator <(PartyMemberIndex a, PartyMemberIndex b)
        {
            return a.Value < b.Value;
        }

        public static bool operator >(PartyMemberIndex a, PartyMemberIndex b)
        {
            return a.Value > b.Value;
        }
    }
}
