using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record DateOfBirth(string Value)
    {
        public const int MaxLength = 8;

        public static DateOfBirth Empty => new DateOfBirth("");

        public static string Culling(string value)
        {
            if (null == value) return null;

            if (value.Length > MaxLength)
            {
                return value.Substring(0, MaxLength);
            }

            return value;
        }

        public override string ToString()
        {
            int year = int.Parse(Value.Substring(0, 4));
            int month = int.Parse(Value.Substring(4, 2));
            int day = int.Parse(Value.Substring(6));

            return ZString.Format("{0}年{1}月{2}日", year, month, day);
        }

        public int ToInt()
        {
            return int.Parse(Value);
        }
    }
}
