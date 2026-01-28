using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models
{
    public record LinkBnIdConfirmModel(
        UserMyId UserMyId,
        UserName UserName,
        UserLevel UserLevel,
        BnIdLinkableFlag IsLinkable,
        BnIdLinkedFlag IsAlreadyLinked,
        BnIdLinkRejectionReasonType BnIdLinkRejectionReasonType,
        BnIdCode BnIdCode)
    {
        public static LinkBnIdConfirmModel Empty { get; } = new LinkBnIdConfirmModel(
            UserMyId.Empty,
            UserName.Empty,
            UserLevel.Empty,
            BnIdLinkableFlag.False,
            BnIdLinkedFlag.False,
            BnIdLinkRejectionReasonType.None,
            BnIdCode.Empty
        );
    }
}
