namespace GLOW.Core.Domain.ValueObjects.InGame
{
    public record SpecialRoleLevelUpAttackElement(
        MasterDataId Id,
        MasterDataId MstAttackElementId,
        AttackPowerParameterValue MinAttackPowerParameter,
        AttackPowerParameterValue MaxAttackPowerParameter,
        EffectiveCount MinEffectiveCount,
        EffectiveCount MaxEffectiveCount,
        TickCount MinStateEffectDuration,
        TickCount MaxStateEffectDuration,
        StateEffectParameter MinStateEffectParameter,
        StateEffectParameter MaxStateEffectParameter)
    {
        public static SpecialRoleLevelUpAttackElement Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            AttackPowerParameterValue.Empty,
            AttackPowerParameterValue.Empty,
            EffectiveCount.Empty,
            EffectiveCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            StateEffectParameter.Empty,
            StateEffectParameter.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
