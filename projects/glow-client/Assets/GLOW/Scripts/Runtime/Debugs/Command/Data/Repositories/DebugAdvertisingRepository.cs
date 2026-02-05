using System.Linq;
using GLOW.Debugs.Command.Data.Data;
using GLOW.Debugs.Command.Domains.Definitions.Repositories;
using GLOW.Debugs.Command.Domains.Models;
using WonderPlanet.InAppAdvertising;

namespace GLOW.Debugs.Command.Data.Repositories
{
    public class DebugAdvertisingRepository : IDebugAdvertisingRepository
    {
        readonly DebugAdUnitData[] _adUnitData = new[]
        {
            new DebugAdUnitData(
                "ca-app-pub-3940256099942544/5224354917",
                "ca-app-pub-3940256099942544/1712485313",
                AdUnitTypes.Reward,
                "AdUnitTypes.Reward1"),
            new DebugAdUnitData(
                "ca-app-pub-3940256099942544/1033173712",
                "ca-app-pub-3940256099942544/4411468910",
                AdUnitTypes.Interstitial,
                "AdUnitTypes.Interstitial1")
        };

        DebugAdUnitModel[] IDebugAdvertisingRepository.GetAdUnits()
        {
            return _adUnitData.Select(data => new DebugAdUnitModel(GetPlatformAdUnitId(data), data.AdUnitType, data.UniqueId)).ToArray();
        }

        DebugAdUnitModel[] IDebugAdvertisingRepository.GetAdUnits(AdUnitTypes adUnitType)
        {
            return _adUnitData.Where(data => data.AdUnitType == adUnitType).Select(data => new DebugAdUnitModel(GetPlatformAdUnitId(data), data.AdUnitType, data.UniqueId)).ToArray();
        }

        DebugAdUnitModel IDebugAdvertisingRepository.GetAdUnit(string uniqueId)
        {
            var data = _adUnitData.FirstOrDefault(ad => ad.UniqueId == uniqueId);
            return data == null ? null : new DebugAdUnitModel(GetPlatformAdUnitId(data), data.AdUnitType, data.UniqueId);
        }

        static string GetPlatformAdUnitId(DebugAdUnitData data)
        {
#if UNITY_ANDROID && !UNITY_EDITOR
            return data.AndroidAdUnit;
#elif UNITY_IOS && !UNITY_EDITOR
            return data.IOSAdUnit;
#else
            return data.AndroidAdUnit;
#endif
        }
    }
}
