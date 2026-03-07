using System;

namespace GLOW.Scenes.MessageBox.Domain.ValueObject
{
    public record MessageActionCompletedFlag(bool Value) : IComparable
    {
        public static MessageActionCompletedFlag True { get; } = new MessageActionCompletedFlag(true);
        public static MessageActionCompletedFlag False { get; } = new MessageActionCompletedFlag(false);
        
        public static implicit operator bool(MessageActionCompletedFlag flag) => flag.Value;

        public int CompareTo(object obj)
        {
            if (obj is MessageActionCompletedFlag other)
            {
                return Value.CompareTo(other.Value);
            }
            return 1;
        }
    }
}