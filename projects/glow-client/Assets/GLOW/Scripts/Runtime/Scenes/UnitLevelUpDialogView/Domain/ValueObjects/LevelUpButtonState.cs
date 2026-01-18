namespace GLOW.Scenes.UnitLevelUpDialogView.Domain.ValueObjects
{
    public record LevelUpButtonState(
        bool EnableMinimum,
        bool EnableMaximum,
        bool EnableMinus,
        bool EnablePlus,
        bool EnableAccept);
}
