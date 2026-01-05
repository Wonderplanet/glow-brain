namespace GLOW.Core.Domain.ValueObjects.StaminaRecover
{
    public record TransitionPossibleFlag(bool Value)
    {
        public static TransitionPossibleFlag True => new(true);
        public static TransitionPossibleFlag False => new(false);
        
        public static implicit operator bool(TransitionPossibleFlag flag) => flag.Value;
    }
}