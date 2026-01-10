using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Models
{
    public record TitleLinkBnIdDialogViewModel(
        UserName UserName,
        UserLevel UserLevel,
        BnIdLinkedFlag BnIdLinkedFlag)
    {
        public static TitleLinkBnIdDialogViewModel Empty { get; } = new TitleLinkBnIdDialogViewModel(
            UserName.Empty,
            UserLevel.Empty,
            BnIdLinkedFlag.False
        );
    }
}
