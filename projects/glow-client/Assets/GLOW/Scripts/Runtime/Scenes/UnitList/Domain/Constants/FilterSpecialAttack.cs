namespace GLOW.Scenes.UnitList.Domain.Constants
{
    public enum FilterSpecialAttack
    {
        All,
        KillerRed,
        KillerBlue,
        KillerGreen,
        KillerYellow,
        KnockBack,
        Drain,
        Stun,
        Freeze,
        Burn,
        Poison,
        Weakening,
        StatusUp,
        StatusDown,
        DamageCut,
        Heal,
        RushAttackPowerUp,
        PlacedItem,
        SpecialAttackCoolTimeShorten,   // TODO: 延長本対応時に追加予定 (SpecialAttackCoolTimeExtend, SummonCoolTimeExtend)
        SummonCoolTimeShorten,
        RemoveBuff,      // プラス効果解除
        RemoveDebuff,    // マイナス効果解除
        Unbeatable,      // 無敵付与
    }
}
