using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Unit
{
    public record UnitRankUpResultModel(UserUnitModel UserUnit, IReadOnlyList<UserItemModel> UserItems);
}
