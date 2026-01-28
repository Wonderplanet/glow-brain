namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> ロールがスペシャルのユニットが必殺技チャージ開始するタイミングでオンになるフラグ変数 </summary>
    public record SpecialUnitSpecialAttackChargeFlag(bool Value)
    {
        public static SpecialUnitSpecialAttackChargeFlag True { get; } = new(true);
        public static SpecialUnitSpecialAttackChargeFlag False { get; } = new(false);

        public static implicit operator bool(SpecialUnitSpecialAttackChargeFlag flag) => flag.Value;

        public static bool operator true(SpecialUnitSpecialAttackChargeFlag flag) => flag.Value;
        public static bool operator false(SpecialUnitSpecialAttackChargeFlag flag) => !flag.Value;
    }
}
