namespace GLOW.Scenes.UnitEnhanceRankUpConfirmDialog.Domain.ValueObjects
{
    public record UnitRankUpEnableConfirm(bool Value)
    {
        public static UnitRankUpEnableConfirm Enable { get; } = new UnitRankUpEnableConfirm(true);
        public static UnitRankUpEnableConfirm Disable { get; } = new UnitRankUpEnableConfirm(false);

        public static UnitRankUpEnableConfirm Create(bool isEnable)
        {
            return isEnable ? Enable : Disable;
        }

        public bool IsEnable()
        {
            return ReferenceEquals(this, Enable);
        }
    }
}
