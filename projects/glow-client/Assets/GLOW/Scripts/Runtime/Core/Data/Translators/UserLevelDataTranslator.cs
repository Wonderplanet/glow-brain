using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class UserLevelDataTranslator
    {
        public static MstUserLevelModel ToUserLevelModel(MstUserLevelData mstUserLevelData, MstUserLevelData mstNextUserLevelData)
        {
            return new MstUserLevelModel(
                new UserLevel(mstUserLevelData.Level),
                new UserExp(mstUserLevelData.Exp),
                new UserExp(mstNextUserLevelData.Exp),
                new Stamina(mstUserLevelData.Stamina));
        }
    }
}
