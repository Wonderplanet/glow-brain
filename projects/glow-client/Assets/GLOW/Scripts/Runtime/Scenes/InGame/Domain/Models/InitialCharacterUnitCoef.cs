namespace GLOW.Scenes.InGame.Domain.Models
{    public record InitialCharacterUnitCoef(
        float StageHpCoef,
        float StageAttackPowerCoef,
        float StageUnitMoveSpeedCoef,
        float InGameSequenceHpCoef,
        float InGameSequenceAttackPowerCoef,
        float InGameSequenceUnitMoveSpeedCoef)
    {
        public static InitialCharacterUnitCoef Empty { get; } = new(1f, 1f, 1f, 1f, 1f, 1f);
    };
}
