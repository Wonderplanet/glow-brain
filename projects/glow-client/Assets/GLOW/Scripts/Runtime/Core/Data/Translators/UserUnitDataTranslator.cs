using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserUnitDataTranslator
    {
        public static UserUnitModel ToUserUnitModel(UsrUnitData data)
        {
            return new UserUnitModel(
                !string.IsNullOrEmpty(data.MstUnitId) ? new MasterDataId(data.MstUnitId) : MasterDataId.Empty,
                !string.IsNullOrEmpty(data.UsrUnitId) ? new UserDataId(data.UsrUnitId) : UserDataId.Empty,
                new UnitLevel(data.Level),
                new UnitRank(data.Rank),
                new UnitGrade(data.GradeLevel),
                new NewEncyclopediaFlag(data.IsNewEncyclopedia));
        }
    }
}
