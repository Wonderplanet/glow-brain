using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Data.Translators
{
    public class UserGachaDataTranslator
    {
        public static UserGachaModel ToUserGachaModel(UsrGachaData data)
        {
            return new UserGachaModel(
                new MasterDataId(data.OprGachaId),
                data.AdPlayedAt ?? DateTimeOffset.MinValue,
                data.PlayedAt ?? DateTimeOffset.MinValue,
                new GachaPlayedCount(data.AdCount),
                new GachaPlayedCount(data.AdDailyCount),
                new GachaPlayedCount(data.Count),
                new GachaPlayedCount(data.DailyCount),
                data.ExpiresAt == null ? GachaExpireAt.Empty : new GachaExpireAt(data.ExpiresAt.Value));
        }
    }
}
