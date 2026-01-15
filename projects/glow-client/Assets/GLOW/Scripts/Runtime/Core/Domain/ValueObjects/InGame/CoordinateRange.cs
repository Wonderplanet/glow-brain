namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// 座標の範囲
    /// </summary>
    public record CoordinateRange(float Min, float Max)
    {
        public static CoordinateRange Empty { get; } = new(0f, 0f);
        public static CoordinateRange Zero { get; } = new(0f, 0f);

        public float Center => (Min + Max) / 2f;
        public float Length => Max - Min;

        public static CoordinateRange BetweenPoints(float a, float b)
        {
            return new CoordinateRange(
                a <= b ? a : b,
                a > b ? a : b
            );
        }

        public static CoordinateRange FromPointAndLength(float point, float length)
        {
            return BetweenPoints(point, point + length);
        }

        public static CoordinateRange Translate(CoordinateRange range, float length)
        {
            return new CoordinateRange(range.Min + length, range.Max + length);
        }

        public static CoordinateRange Intersect(CoordinateRange a, CoordinateRange b)
        {
            if (!IsIntersectOrTangency(a, b))
            {
                return Empty;
            }

            return new CoordinateRange(
                a.Min <= b.Min ? b.Min : a.Min,
                a.Max <= b.Max ? a.Max : b.Max
            );
        }

        public static bool IsIntersectOrTangency(CoordinateRange a, CoordinateRange b)
        {
            return a.Max >= b.Min && b.Max >= a.Min;
        }

        public static bool IsIntersect(CoordinateRange a, CoordinateRange b)
        {
            return a.Max > b.Min && b.Max > a.Min;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsInRange(float value)
        {
            return Min <= value && value <= Max;
        }
    }
}
