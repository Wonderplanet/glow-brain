using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record InGameAutoEnabledFlag(bool Value)
    {
        public static InGameAutoEnabledFlag True { get; } = new(true);
        public static InGameAutoEnabledFlag False { get; } = new(false);

        public static implicit operator bool(InGameAutoEnabledFlag flag) => flag.Value;
        public static InGameAutoEnabledFlag operator !(InGameAutoEnabledFlag flag) => flag.Value ? False : True;
        
        public AutoPlayerEnabledFlag ToAutoPlayerEnabledFlag() => Value
            ? AutoPlayerEnabledFlag.True
            : AutoPlayerEnabledFlag.False;
    }
}