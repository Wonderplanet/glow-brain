namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleSpecificEnemyDestructionFlag(bool Value)
    {
        public static InGameSpecialRuleSpecificEnemyDestructionFlag True { get; } = new (true);
        public static InGameSpecialRuleSpecificEnemyDestructionFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleSpecificEnemyDestructionFlag flag) => flag.Value;
    }
}