using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UserNameEdit.Domain.Models
{
    public record UserNameEditDialogModel(
        UserName UserName,
        bool IsCanChangeName,
        RemainingTimeSpan RemainingTimeSpan);
}
