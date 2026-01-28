using GLOW.Debugs.Command.Domains.Models;
using WonderPlanet.InAppAdvertising;

namespace GLOW.Debugs.Command.Domains.Definitions.Repositories
{
    public interface IDebugAdvertisingRepository
    {
        DebugAdUnitModel[] GetAdUnits();
        DebugAdUnitModel[] GetAdUnits(AdUnitTypes adUnitType);
        DebugAdUnitModel GetAdUnit(string uniqueId);
    }
}
