namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleEnemyDestructionFlag(bool Value)
    {
        public static InGameSpecialRuleEnemyDestructionFlag True { get; } = new (true);
        public static InGameSpecialRuleEnemyDestructionFlag False { get; } = new (false);

        public static implicit operator bool(InGameSpecialRuleEnemyDestructionFlag flag) => flag.Value;
    }
}