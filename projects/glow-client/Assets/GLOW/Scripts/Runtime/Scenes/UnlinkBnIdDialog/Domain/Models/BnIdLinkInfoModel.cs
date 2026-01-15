using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnlinkBnIdDialog.Domain.Models
{
    public record BnIdLinkInfoModel(
        RemainingTimeSpan UnlinkBnIdTimeSpan);
}
