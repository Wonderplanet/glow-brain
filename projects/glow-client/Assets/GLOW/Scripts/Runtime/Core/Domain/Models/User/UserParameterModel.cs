using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using WPFramework.Constants.Platform;

namespace GLOW.Core.Domain.Models
{
    public record UserParameterModel(
        UserLevel Level,
        UserExp Exp,
        Coin Coin,
        Stamina CurrentStamina,
        DateTimeOffset? StaminaUpdatedAt,
        FreeDiamond FreeDiamond,
        PaidDiamondIos PaidDiamondIos,
        PaidDiamondAndroid PaidDiamondAndroid,
        UserDailyBuyStamina UserDailyBuyStamina)
    {

        public static UserParameterModel Empty { get; } = new UserParameterModel(
            UserLevel.Empty,
            UserExp.Zero,
            Coin.Zero,
            Stamina.Empty,
            null,
            FreeDiamond.Empty,
            PaidDiamondIos.Empty,
            PaidDiamondAndroid.Empty,
            UserDailyBuyStamina.Empty
        );

        //内部的に保存して、安易に更新されたくない・参照されたくないものは、メソッド経由で返す
        public Stamina CurrentStamina { get; } = CurrentStamina;

        PaidDiamondIos PaidDiamondIos { get; } = PaidDiamondIos;
        PaidDiamondAndroid PaidDiamondAndroid { get; } = PaidDiamondAndroid;

        public PaidDiamond GetPaidDiamondFromPlatform(string platformId)
        {
            if (platformId == PlatformId.Android) return new PaidDiamond(PaidDiamondAndroid.Value);
            else if (platformId == PlatformId.IOS) return new PaidDiamond(PaidDiamondIos.Value);
            else throw new Exception("Invalid PlatformId");
        }
        public TotalDiamond GetTotalDiamond(string platformId)
        {
            var paidDiamond = GetPaidDiamondFromPlatform(platformId);
            return new TotalDiamond(FreeDiamond.Value + paidDiamond.Value);
        }
    }
}
