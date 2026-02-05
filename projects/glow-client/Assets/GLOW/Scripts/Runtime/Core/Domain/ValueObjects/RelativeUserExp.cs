namespace GLOW.Core.Domain.ValueObjects
{
    /// <summary>
    /// 現在レベルにおける相対経験値
    /// （あるレベルにちょうど上がったときの経験値を0としたときの経験値）
    /// </summary>
    /// <param name="Value"></param>
    public record RelativeUserExp(long Value)
    {
        public static RelativeUserExp Empty { get; } = new(0);
        public static RelativeUserExp operator -(RelativeUserExp a, RelativeUserExp b) => new(a.Value - b.Value);
        public static float operator /(RelativeUserExp a, RelativeUserExp b) => (float)a.Value / b.Value;

        public override string ToString()
        {
            return Value.ToString("N0");
        }

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
