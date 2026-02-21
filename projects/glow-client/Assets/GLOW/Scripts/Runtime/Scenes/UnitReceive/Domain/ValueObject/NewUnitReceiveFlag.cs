namespace GLOW.Scenes.UnitReceive.Domain.ValueObject
{
    public record NewUnitReceiveFlag(bool Value)
    {
        public static NewUnitReceiveFlag Empty { get; } = new(false);
        public static NewUnitReceiveFlag True { get; } = new(true);
        public static NewUnitReceiveFlag False { get; } = new(false);
        
        public static implicit operator bool(NewUnitReceiveFlag flag) => flag.Value;
    }
}