using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IGameApiRequestHeaderAssignor
    {
        void SetRequestHeaders(ServerApi context, GameVersionModel gameVersionModel);
        void SetRequestHeaders(ServerApi context, AdvertisingId advertisingId, CountryCode countryCode);
    }
}
