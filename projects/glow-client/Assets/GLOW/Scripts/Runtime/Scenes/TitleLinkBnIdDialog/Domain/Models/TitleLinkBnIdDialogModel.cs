using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models
{
    public record TitleLinkBnIdDialogModel(
        UserName UserName,
        UserLevel UserLevel,
        BnIdLinkedFlag BnIdLinkedFlag
    )
    {
        public static TitleLinkBnIdDialogModel Empty { get; } = new TitleLinkBnIdDialogModel(
            UserName.Empty,
            UserLevel.Empty,
            BnIdLinkedFlag.False
        );
    }
}
