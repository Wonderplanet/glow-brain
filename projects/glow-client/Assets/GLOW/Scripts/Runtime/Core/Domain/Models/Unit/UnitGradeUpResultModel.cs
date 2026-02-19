using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Unit
{
    public record UnitGradeUpResultModel(UserUnitModel UserUnit, IReadOnlyList<UserItemModel> UserItems);
}
