namespace GLOW.Scenes.BattleResult.Domain.ValueObjects
{
    public record InGameStageValidFlag(bool Value)
    {
        public static InGameStageValidFlag True { get; } = new InGameStageValidFlag(true);
        public static InGameStageValidFlag False { get; } = new InGameStageValidFlag(false);
        
        public static implicit operator bool(InGameStageValidFlag flag) => flag.Value;
    }
}