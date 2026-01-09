using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserNameEdit.Presentation.ViewModels
{
    public record UserNameEditDialogViewModel(
        UserName UserName,
        bool IsCanChangeName,
        RemainingTimeSpan RemainingTimeSpan);
}
