using GLOW.Scenes.UnitList.Domain.Constants;

namespace GLOW.Scenes.UnitList.Presentation.Extension
{
    public static class FilterSpecialAttackExtension
    {
        public static string ToDisplayText(this FilterSpecialAttack filterSpecialAttack)
        {
            return filterSpecialAttack switch
            {
                FilterSpecialAttack.All => "すべて",
                FilterSpecialAttack.KillerRed => "赤属性特攻ダメージ",
                FilterSpecialAttack.KillerBlue => "青属性特攻ダメージ",
                FilterSpecialAttack.KillerGreen => "緑属性特攻ダメージ",
                FilterSpecialAttack.KillerYellow => "黄属性特攻ダメージ",
                FilterSpecialAttack.KnockBack => "ノックバック攻撃",
                FilterSpecialAttack.Drain => "体力吸収攻撃",
                FilterSpecialAttack.Stun => "スタン攻撃",
                FilterSpecialAttack.Freeze => "氷結攻撃",
                FilterSpecialAttack.Burn => "火傷ダメージ付与",
                FilterSpecialAttack.Poison => "毒ダメージ付与",
                FilterSpecialAttack.Weakening => "弱体化攻撃",
                FilterSpecialAttack.StatusUp => "ステータスUP",
                FilterSpecialAttack.StatusDown => "ステータスDOWN",
                FilterSpecialAttack.DamageCut => "被ダメージカット",
                FilterSpecialAttack.Heal => "体力回復",
                FilterSpecialAttack.RushAttackPowerUp => "RUSHダメージUP",
                FilterSpecialAttack.PlacedItem => "オブジェクト生成",
                FilterSpecialAttack.SpecialAttackCoolTimeShorten => "必殺ワザ発動時間短縮",
                FilterSpecialAttack.SummonCoolTimeShorten => "再召喚時間短縮",
                _ => ""
            };
        }
    }
}
