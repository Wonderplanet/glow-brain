using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaProbabilityGroupModel(Rarity Rarity, IReadOnlyList<GachaPrizeModel> Prizes);
}
