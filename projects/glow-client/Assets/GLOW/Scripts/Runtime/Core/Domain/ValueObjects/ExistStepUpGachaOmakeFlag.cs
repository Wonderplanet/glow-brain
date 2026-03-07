namespace GLOW.Core.Domain.ValueObjects
{
    public record ExistStepUpGachaOmakeFlag(bool Value)
    {
        public static ExistStepUpGachaOmakeFlag True { get; } = new ExistStepUpGachaOmakeFlag(true);
        public static ExistStepUpGachaOmakeFlag False { get; } = new ExistStepUpGachaOmakeFlag(false);
        
        public static implicit operator bool(ExistStepUpGachaOmakeFlag flag) => flag.Value;
        
        public bool IsTrue() => Value;
    }
}