namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Domain.ValueObjects
{
    public record OutpostEnhanceLevelUpButtonState(
        bool EnableMinimum,
        bool EnableMaximum,
        bool EnableMinus,
        bool EnablePlus,
        bool EnableAccept);
}
