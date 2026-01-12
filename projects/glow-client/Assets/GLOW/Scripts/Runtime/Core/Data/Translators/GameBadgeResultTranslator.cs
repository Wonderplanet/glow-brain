using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Data.Translators
{
    public class GameBadgeResultTranslator
    {
        public static GameBadgeResultModel ToGameBadgeResultModel(GameBadgeResultData data)
        {

            var badgeModel = BadgeDataTranslator.ToBadgeModel(data.Badges);
            var mngContentCloseModels = data.MngContentCloses
                                            ?.Select(MngContentCloseDataTranslator.ToMngContentCloseModel)
                                            .ToList()
                                        ?? new List<MngContentCloseModel>();

            return new GameBadgeResultModel(badgeModel, mngContentCloseModels);
        }
    }
}
