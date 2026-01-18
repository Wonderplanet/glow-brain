namespace WPFramework.Modules.Date
{
    public record DatePivotSettings(
        int TimeZoneOffset,
        int HourOffset,
        int MinuteOffset
    )
    {
        public int TimeZoneOffset { get; } = TimeZoneOffset;
        public int HourOffset { get; } = HourOffset;
        public int MinuteOffset { get; } = MinuteOffset;
    }
}
