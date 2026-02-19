namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    /// <summary> ロールがスペシャルのユニットが必殺技発動するタイミングでオンになるフラグ変数 </summary>
    public record SpecialUnitUseSpecialAttackFlag(bool Value)
    {
        public static SpecialUnitUseSpecialAttackFlag True { get; } = new(true);
        public static SpecialUnitUseSpecialAttackFlag False { get; } = new(false);

        public static implicit operator bool(SpecialUnitUseSpecialAttackFlag flag) => flag.Value;

        public static bool operator true(SpecialUnitUseSpecialAttackFlag flag) => flag.Value;
        public static bool operator false(SpecialUnitUseSpecialAttackFlag flag) => !flag.Value;
    }
}
