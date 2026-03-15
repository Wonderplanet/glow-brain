namespace GLOW.Modules.GameOption.Domain.ValueObjects
{
    // ダメージ表示のON/OFFを表すフラグ
    public record DamageDisplayFlag(bool Value)
    {
        public static DamageDisplayFlag True { get; } = new(true);
        public static DamageDisplayFlag False { get; } = new(false);

        public static implicit operator bool(DamageDisplayFlag flag) => flag.Value;
        public static DamageDisplayFlag operator !(DamageDisplayFlag flag) => new(!flag.Value);
    }
}

