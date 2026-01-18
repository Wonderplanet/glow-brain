using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using WonderPlanet.AnalyticsBridge;
using WonderPlanet.AnalyticsBridge.Adjust;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class GameApiContextHeaderModifier : IGameApiContextHeaderModifier
    {
        [Inject] ICommonRequestHeaderAssignor CommonRequestHeaderAssignor { get; }
        [Inject] IGameApiRequestHeaderAssignor GameApiRequestHeaderAssignor { get; }
        [Inject] AnalyticsCenter AnalyticsCenter { get; }

        async UniTask IGameApiContextHeaderModifier.Configure(ServerApi context, CancellationToken cancellationToken)
        {
            CommonRequestHeaderAssignor.SetRequestHeaders(context);
            await SetRequestHeadersByGameAPIRequestHeaderAssignor(context, cancellationToken);
        }

        async UniTask SetRequestHeadersByGameAPIRequestHeaderAssignor(
            ServerApi context,
            CancellationToken cancellationToken)
        {
            // 広告ID
            var adIdString = await AnalyticsCenter.GetAgent<AdjustAgent>().GetAdvertisingId(cancellationToken);
            var adId = new AdvertisingId(adIdString);
            
            // 国コード
            var countryCode = new CountryCode(System.Globalization.RegionInfo.CurrentRegion.Name);
            
            GameApiRequestHeaderAssignor.SetRequestHeaders(context, adId, countryCode);
        }
    }
}