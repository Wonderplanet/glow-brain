using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Unit
{
    public record SpecialAttackInfoModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime CoolTime,
        IReadOnlyList<SpecialAttackInfoGradeModel> RankModelList)
    {
        public static SpecialAttackInfoModel Empty { get; } = new (
            SpecialAttackName.Empty, 
            SpecialAttackInfoDescription.Empty, 
            SpecialAttackCoolTime.Empty, 
            new List<SpecialAttackInfoGradeModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
