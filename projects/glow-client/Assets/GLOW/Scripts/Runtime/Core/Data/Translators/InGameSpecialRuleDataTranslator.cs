using System;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class InGameSpecialRuleDataTranslator
    {
        public static MstInGameSpecialRuleModel ToInGameSpecialRuleModel(MstInGameSpecialRuleData data)
        {
            return new MstInGameSpecialRuleModel(
                new MasterDataId(data.Id),
                data.ContentType,
                new MasterDataId(data.TargetId),
                data.RuleType,
                new EventRuleValue(data.RuleValue),
                data.StartAt,
                data.EndAt);
        }
    }
}
