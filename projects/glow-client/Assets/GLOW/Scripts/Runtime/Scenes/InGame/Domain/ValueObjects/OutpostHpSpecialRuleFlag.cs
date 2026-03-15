namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> ゲートHPが特別ルールによって設定されたかどうかを判別するフラグ </summary>
    public record OutpostHpSpecialRuleFlag(bool Value)
    {
        public static OutpostHpSpecialRuleFlag True { get; } = new (true);
        public static OutpostHpSpecialRuleFlag False { get; } = new (false);
        public static implicit operator bool(OutpostHpSpecialRuleFlag flag) => flag.Value;
    }
}
