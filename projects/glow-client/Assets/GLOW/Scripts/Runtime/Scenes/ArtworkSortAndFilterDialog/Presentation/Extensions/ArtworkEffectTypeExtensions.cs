using System;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Extensions
{
    public class ArtworkEffectTypeExtensions
    {
        public static string ToDisplayString(ArtworkEffectType type)
        {
            return type switch
            {
                ArtworkEffectType.AttackPowerUp => "攻撃UP",
                ArtworkEffectType.HpUp => "体力UP",
                ArtworkEffectType.ResummonSpeedUp => "再召喚時間短縮",
                ArtworkEffectType.SpecialAttackChargeSpeedUp => "必殺ワザ発動時間短縮",
                ArtworkEffectType.InitialLeaderPointUp => "バトル開始時所持リーダーP数UP",
                ArtworkEffectType.JumbleRushChargeSpeedUp => "RUSH発動時間短縮",
                ArtworkEffectType.JumbleRushDamageUp => "RUSHダメージUP",
                _ => throw new ArgumentOutOfRangeException(nameof(type)),
            };
        }
    }
}
