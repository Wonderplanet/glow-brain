namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary> スペシャルユニットでの必殺技使用時にコマ選択を必要とする効果範囲のタイプかのフラグ </summary>
    public record NeedTargetSelectTypeFlag(bool Value)
    {
        public static NeedTargetSelectTypeFlag True { get; } = new(true);
        public static NeedTargetSelectTypeFlag False { get; } = new(false);

        public static implicit operator bool(NeedTargetSelectTypeFlag flag) => flag.Value;
    }
}
