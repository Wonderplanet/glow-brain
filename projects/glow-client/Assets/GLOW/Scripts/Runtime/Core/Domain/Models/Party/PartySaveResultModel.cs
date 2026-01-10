using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Party
{
    public record PartySaveResultModel(IReadOnlyList<UserPartyModel> Parties);
}
