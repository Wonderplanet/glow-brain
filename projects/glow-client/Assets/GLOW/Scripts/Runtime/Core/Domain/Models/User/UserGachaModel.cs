using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record UserGachaModel(
        MasterDataId OprGachaId,
        DateTimeOffset AdPlayedAt,
        DateTimeOffset PlayedAt,
        GachaPlayedCount AdPlayedCount,
        GachaPlayedCount DailyAdPlayedCount,
        GachaPlayedCount PlayedCount,
        GachaPlayedCount DailyPlayedCount,
        GachaExpireAt GachaExpireAt
        )
    {

        public static UserGachaModel Empty = new (
            MasterDataId.Empty,
            DateTimeOffset.MinValue,// TODO:開始前の日付追加したら変更
            DateTimeOffset.MinValue,
            GachaPlayedCount.Zero,
            GachaPlayedCount.Zero,
            GachaPlayedCount.Zero,
            GachaPlayedCount.Zero,
            GachaExpireAt.Empty
        );

        // 回していないガシャはユーザーデータ無いのでここでCreateする
        // 例外：スタートダッシュガシャは初回チュートリアル完了時に回して無くても追加される
        public static UserGachaModel CreateById(MasterDataId gachaId)
        {
            var userGachaModel = new UserGachaModel(
                gachaId,
                DateTimeOffset.MinValue,
                DateTimeOffset.MinValue,
                GachaPlayedCount.Zero,
                GachaPlayedCount.Zero,
                GachaPlayedCount.Zero,
                GachaPlayedCount.Zero,
                GachaExpireAt.Empty
            );

            return userGachaModel;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
