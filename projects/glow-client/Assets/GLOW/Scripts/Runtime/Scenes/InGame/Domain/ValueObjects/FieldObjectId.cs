namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record FieldObjectId(int Value)
    {
        public static FieldObjectId Empty { get; } = new(0);

        public static bool operator <(FieldObjectId a, FieldObjectId b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(FieldObjectId a, FieldObjectId b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(FieldObjectId a, FieldObjectId b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(FieldObjectId a, FieldObjectId b)
        {
            return a.Value >= b.Value;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }
    }
}
