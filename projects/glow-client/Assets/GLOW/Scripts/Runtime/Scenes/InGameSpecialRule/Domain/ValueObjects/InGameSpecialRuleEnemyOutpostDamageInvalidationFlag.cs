namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleEnemyOutpostDamageInvalidationFlag(bool Value)
    {
        public static InGameSpecialRuleEnemyOutpostDamageInvalidationFlag True { get; } = new (true);
        public static InGameSpecialRuleEnemyOutpostDamageInvalidationFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleEnemyOutpostDamageInvalidationFlag flag) => flag.Value;
    }
}