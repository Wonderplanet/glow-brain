using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.UnitEnhance.Domain.Models
{
    public record UnitEnhanceRankUpResultModel(UserDataId UserUnitId, UnitRank BeforeRank, UnitRank AfterRank);
}
