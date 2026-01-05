using WonderPlanet.InAppAdvertising;

namespace GLOW.Debugs.Command.Domains.Models
{
    public record DebugAdUnitModel(string AdUnit, AdUnitTypes AdUnitType, string UniqueId)
    {
        public string AdUnit { get; } = AdUnit;
        public AdUnitTypes AdUnitType { get; } = AdUnitType;
        public string UniqueId { get; } = UniqueId;
    }
}
