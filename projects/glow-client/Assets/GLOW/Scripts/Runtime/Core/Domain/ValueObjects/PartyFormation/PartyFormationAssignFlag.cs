namespace GLOW.Core.Domain.ValueObjects
{
    public record PartyFormationAssignFlag(bool Value)
    {
        public static PartyFormationAssignFlag True { get; } = new PartyFormationAssignFlag(true);
        public static PartyFormationAssignFlag False { get; } = new PartyFormationAssignFlag(false);

        public static implicit operator bool(PartyFormationAssignFlag flag) => flag.Value;
    }
}

