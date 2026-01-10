using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.ViewModels
{
    public record TitleBnIdLinkageResultDialogViewModel(
        TitleBnIdLinkageResultTitle Title,
        TitleBnIdLinkageResultMessage Message,
        TitleBnIdLinkageResultDateTitle DateTitle,
        UserMyId MyId,
        UserName Name,
        UserLevel Level,
        TitleBnIdLinkageResultAttentionMessage AttentionMessage,
        TitleBnIdLinkageResultLeftButtonTitle LeftButtonTitle,
        TitleBnIdLinkageResultRightButtonTitle RightButtonTitle)
    {
        public static TitleBnIdLinkageResultDialogViewModel Empty { get; } = new TitleBnIdLinkageResultDialogViewModel(
            TitleBnIdLinkageResultTitle.Empty,
            TitleBnIdLinkageResultMessage.Empty,
            TitleBnIdLinkageResultDateTitle.Empty,
            UserMyId.Empty,
            UserName.Empty,
            UserLevel.Empty,
            TitleBnIdLinkageResultAttentionMessage.Empty,
            TitleBnIdLinkageResultLeftButtonTitle.Empty,
            TitleBnIdLinkageResultRightButtonTitle.Empty
        );
    }
}
