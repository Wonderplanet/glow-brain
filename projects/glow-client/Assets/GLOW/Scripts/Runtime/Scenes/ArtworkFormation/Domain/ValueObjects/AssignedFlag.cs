namespace GLOW.Scenes.ArtworkFormation.Domain.ValueObjects
{
    public record AssignedFlag(bool Value)
    {
        public static AssignedFlag Assigned => new AssignedFlag(true);
        public static AssignedFlag Unassigned => new AssignedFlag(false);
        
        public static implicit operator bool(AssignedFlag flag) => flag.Value;
        
        public bool IsAssigned => Value;
    }
}