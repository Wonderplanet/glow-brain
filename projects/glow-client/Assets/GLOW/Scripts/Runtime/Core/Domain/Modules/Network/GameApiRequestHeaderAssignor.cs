using System.Collections.Generic;
using System.Text;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using UnityHTTPLibrary;
using WPFramework.Modules.Log;

namespace GLOW.Core.Domain.Modules.Network
{
    public sealed class GameApiRequestHeaderAssignor : IGameApiRequestHeaderAssignor
    {
        void IGameApiRequestHeaderAssignor.SetRequestHeaders(ServerApi context, GameVersionModel gameVersionModel)
        {
            // NOTE: GameAPIのヘッダーにリソースマスターデータのリリースキーとハッシュを追加する
            var headers = new Dictionary<string, string>(context.AdditionalRequestHeaders ?? new Dictionary<string, string>());
            // NOTE: 既に記述されていたら上書きする
            headers[RequestHeader.Game.OprHash] = gameVersionModel.OprHash;
            headers[RequestHeader.Game.MstHash] = gameVersionModel.MstHash;
            headers[RequestHeader.Game.MstI18nHash] = gameVersionModel.MstI18nHash;
            headers[RequestHeader.Game.OprI18nHash] = gameVersionModel.OprI18nHash;
            headers[RequestHeader.Game.AssetHash] = gameVersionModel.AssetHash;

            var builder = new StringBuilder();
            foreach (var header in headers)
            {
                builder.AppendLine($"{header.Key}:{header.Value}");
            }
            ApplicationLog.Log(nameof(GameApiRequestHeaderAssignor), $"Set Request Headers\n{builder}");

            context.AdditionalRequestHeaders = headers;
        }

        public void SetRequestHeaders(ServerApi context, AdvertisingId advertisingId, CountryCode countryCode)
        {
            var headers = new Dictionary<string, string>(context.AdditionalRequestHeaders ?? new Dictionary<string, string>());

            headers[RequestHeader.Game.AdId] = advertisingId.Value;
            headers[RequestHeader.Game.CountryCode] = countryCode.Value;

            var builder = new StringBuilder();
            foreach (var header in headers)
            {
                builder.AppendLine($"{header.Key}:{header.Value}");
            }
            ApplicationLog.Log(nameof(GameApiRequestHeaderAssignor), $"SetRequestHeaders\n{builder}");

            context.AdditionalRequestHeaders = headers;
        }
    }
}
