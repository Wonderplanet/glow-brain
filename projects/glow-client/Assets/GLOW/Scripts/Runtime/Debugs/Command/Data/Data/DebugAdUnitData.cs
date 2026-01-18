using WonderPlanet.InAppAdvertising;

namespace GLOW.Debugs.Command.Data.Data
{
    public record DebugAdUnitData(string AndroidAdUnit, string IOSAdUnit, AdUnitTypes AdUnitType, string UniqueId)
    {
        public string AndroidAdUnit { get; } = AndroidAdUnit;
        public string IOSAdUnit { get; } = IOSAdUnit;
        public AdUnitTypes AdUnitType { get; } = AdUnitType;
        public string UniqueId { get; } = UniqueId;
    }
}
