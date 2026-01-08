using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnlinkBnIdDialog.Presentation.Models
{
    public record BnIdLinkInfoViewModel(
        RemainingTimeSpan UnlinkBnIdTimeSpan)
    {
        public static BnIdLinkInfoViewModel Empty { get; } = new BnIdLinkInfoViewModel(
            RemainingTimeSpan.Empty
        );
    }
}
