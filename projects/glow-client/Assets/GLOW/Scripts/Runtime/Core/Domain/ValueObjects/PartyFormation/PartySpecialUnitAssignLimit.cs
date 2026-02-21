namespace GLOW.Core.Domain.ValueObjects
{
    /// <summary> パーティ編成でのスペシャルユニットの編成制限数 </summary>
    public record PartySpecialUnitAssignLimit(int Value)
    {
        public static PartySpecialUnitAssignLimit Empty = new PartySpecialUnitAssignLimit(0);

        public static bool operator >=(PartySpecialUnitAssignLimit a, int b) => a.Value >= b;
        public static bool operator <=(PartySpecialUnitAssignLimit a, int b) => a.Value <= b;

        public int ToInt()
        {
            return Value;
        }
    }
}
