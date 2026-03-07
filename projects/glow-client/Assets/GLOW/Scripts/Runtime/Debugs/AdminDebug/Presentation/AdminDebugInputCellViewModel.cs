namespace GLOW.Debugs.AdminDebug.Presentation
{
    public record AdminDebugInputCellViewModel(
        string Name,
        string Type,
        int? Min,
        int? Max,
        string Description)
    {
        public string Name { get; } = Name;
        public string Type { get; } = Type;
        public int? Min { get; } = Min;
        public int? Max { get; } = Max;
        public string Description { get; } = Description;
    }
}
